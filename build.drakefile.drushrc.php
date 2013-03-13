<?php

$api = 1;

/*
 * The first part here is just to have something to test with. It could as well
 * have been in another file, but it's just easier to work in a single file (for
 * the moment). I'm using IDA as a test base, but any site will do.
 *
 * Putting this stuff in the context to ease overriding.
 */
$context = array(
  'root' => '/var/www/ida/profiles/ida',
  'verbose' => FALSE,
);

/*
 * Some tasks that invoke the actions we're defining.
 */
$tasks['ida-lint'] = array(
  'action' => 'php-lint',
  'files' => fileset('php-custom'),
  'verbose' => context('verbose'),
);

$tasks['ida-debug'] = array(
  'action' => 'php-debug',
  'files' => fileset('php-custom'),
  'verbose' => context('verbose'),
);

/*
 * Filesets for the tasks.
 */
$filesets['php'] = array(
  'include' => array('*.module', '*.inc', '*.php'),
);

$filesets['php-custom'] = array(
  'dir' => context('root'),
  'extend' => 'php',
  'exclude' => array('**/contrib/**', '**/libraries/**'),
);

/**
 * It's here things get interesting, here the actual testing actions are
 * defined.
 */

/**
 * PHP lint action. Runs the files through PHP to check for syntax errors.
 */
$actions['php-lint'] = array(
  'default_message' => 'PHP linting files',
  'callback' => 'drake_php_lint',
  'parameters' => array(
    'files' => 'Files to lint.',
    'verbose' => array(
      'description' => 'Print all files processed.',
      'default' => FALSE,
    ),
  ),
);

function drake_php_lint($context) {
  $command = 'php 2>&1 -n -l ';
  // @todo the following makes PHP report everything, including deprecated
  // code. Add as an option.
  // $command .= '-d error_reporting=32767 ';
  foreach ($context['files'] as $file) {
    if ($context['verbose']) {
      drush_log(dt('Linting  @file', array('@file' => $file->path())), 'status');
    }
    drush_shell_exec($command . '"' . $file . '"');
    $messages = drush_shell_exec_output();

    $bad_files = array();
    foreach ($messages as $message) {
      if (trim($message) == '') {
        continue;
      }
      if ((!preg_match('/^(.*)Deprecated:/', $message) || $this->deprecatedAsError) && !preg_match('/^No syntax errors detected/', $message)) {
        if (!isset($bad_files[(string) $file])) {
          $bad_files[(string) $file] = array();
        }

        array_push($bad_files, $message);
        drush_log($message, 'error');
      }
    }
    if (sizeof($bad_files)) {
      drake_action_error('Syntax error in files.');
      return;
    }
  }
}


/**
 * PHP debug statement check action. Greps files for common debug statements.
 */
$actions['php-debug'] = array(
  'default_message' => 'Checking PHP files for debug statements',
  'callback' => 'drake_php_debug',
  'parameters' => array(
    'files' => 'Files to check for debugging statements.',
    'verbose' => array(
      'description' => 'Print all files processed.',
      'default' => FALSE,
    ),
  ),
);

function drake_php_debug($context) {
  // @todo Make this configurable through the action.
  $debug = array(
    ' dsm\(',
    ' dpm\(',
    ' dpr\(',
    ' dprint_r\(',
    ' db_queryd\(',
    ' krumo',
    ' kpr\(',
    ' kprint_r\(',
    ' var_dump\(',
    ' dd\(',
    ' drupal_debug\(',
    ' dpq\(',

  );
  $command = 'grep -nHE "(' . join('|', $debug) . ')" ';
  foreach ($context['files'] as $file) {
    // exec($command.'"'.$file.'" 2>&1', $messages);
    if ($context['verbose']) {
      drush_log(dt('Checking @file', array('@file' => $file->path())), 'status');
    }
    drush_shell_exec($command . '"' . $file . '"');
    $messages = drush_shell_exec_output();

    $bad_files = array();
    foreach ($messages as $message) {
      if (trim($message) == '') {
        continue;
      }
      array_push($bad_files, $message);
      drush_log($message, 'error');
    }
    if (sizeof($bad_files)) {
      drake_action_error('Debug statements found in files.');
      return;
    }
  }
}
