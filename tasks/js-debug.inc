<?php

/**
 * @file
 * Check JS files for debug statements, using grep.
 */

/**
 * Task.
 */
$tasks['js-debug'] = array(
  'action' => 'js-debug',
  'files' => fileset('js-custom'),
  'verbose' => context_optional('verbose'),
);

/**
 * Action.
 */
$actions['js-debug'] = array(
  'default_message' => 'Checking JS files for debug statements',
  'callback' => 'drake_ci_js_debug',
  'parameters' => array(
    'files' => 'Files to check for debug statements.',
    'verbose' => array(
      'description' => 'Print all files processed.',
      'default' => FALSE,
    ),
  ),
);

/**
 * Action callback; check JS files for common debug statements.
 */
function drake_ci_js_debug($context) {
  // @todo Make this configurable through the action.
  $debug = array(
    ' console.log\(',
  );
  $overall_status = 'ok';

  foreach ($context['files'] as $file) {
    if ($context['verbose']) {
      drush_log(dt('Checking @file', array('@file' => $file->path())), 'status');
    }
    if (!drake_ci_shell_exec('grep -nHE "(%s)" "%s"', implode('|', $debug), $file)) {
      return FALSE;
    }

    $messages = drush_shell_exec_output();

    $bad_files = array();
    foreach ($messages as $message) {
      if (trim($message) == '') {
        continue;
      }
      array_push($bad_files, $message);
      drush_log($message, 'error');
    }
    if (count($bad_files)) {
      drake_action_error(dt('Debug statements found in files.'));
      return;
    }
  }
}
