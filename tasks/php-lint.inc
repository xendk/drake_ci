<?php

/**
 * @file
 * Runs the files through the PHP cli command to check for syntax errors.
 */

/**
 * Task.
 */
$tasks['php-lint'] = array(
  'action' => 'php-lint',
  'files' => fileset('php-custom'),
  'verbose' => context_optional('verbose'),
);

/**
 * Action.
 */
$actions['php-lint'] = array(
  'default_message' => 'PHP linting files',
  'callback' => 'drake_ci_php_lint',
  'parameters' => array(
    'files' => 'Files to lint.',
    'verbose' => array(
      'description' => 'Print all files processed.',
      'default' => FALSE,
    ),
  ),
);

/**
 * Action callback; lint PHP files.
 */
function drake_ci_php_lint($context) {
  foreach ($context['files'] as $file) {
    if ($context['verbose']) {
      drush_log(dt('Linting  @file', array('@file' => $file->path())), 'status');
    }
    // @todo the following makes PHP report everything, including deprecated
    // code. Add as an option.
    // $command .= '-d error_reporting=32767 ';
    if (!drake_ci_shell_exec('php 2>&1 -n -l "%s"', $file)) {
      return FALSE;
    }
    $messages = drush_shell_exec_output();

    $bad_files = array();
    foreach ($messages as $message) {
      if (trim($message) == '') {
        continue;
      }
      // @todo: if checking for deprecated code:
      //   preg_match('/^(.*)Deprecated:/', $message)
      if (!preg_match('/^No syntax errors detected/', $message)) {
        if (!isset($bad_files[(string) $file])) {
          $bad_files[(string) $file] = array();
        }

        array_push($bad_files, $message);
        drush_log($message, 'error');
      }
    }
    if (count($bad_files)) {
      drake_action_error(dt('Syntax error in files.'));
      return;
    }
  }
}
