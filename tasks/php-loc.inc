<?php

/**
 * @file
 * Analyse PHP code for size and structure using phploc..
 *
 * Install phploc:
 *   $ sudo pear channel-discover pear.phpunit.de
 *   $ sudo pear install --alldeps phpunit/phploc
*/

/**
 * Task.
 */
$tasks['php-loc'] = array(
  'action' => 'php-loc',
  'files' => fileset('php-custom'),
  'output-dir' => context_optional('output-dir'),
);

/**
 * Action.
 */
$actions['php-loc'] = array(
  'default_message' => 'PHP LOC analysis.',
  'callback' => 'drake_ci_php_loc',
  'parameters' => array(
    'files' => 'Files to analyse.',
    'output-dir' => array(
      'description' => 'Output CSV files here.',
      'default' => '',
    ),
  ),
);


/**
 * Action callback; check PHP files for duplicate code.
 */
function drake_ci_php_loc($context) {
  $filenames = array();
  foreach ($context['files'] as $file) {
    $filenames[] = drush_escapeshellarg($file->fullPath());
  }

  $report_options = '';
  if (!empty($context['output-dir'])) {
    $report_options = '--log-csv ' . $context['output-dir'] . '/phploc.csv';
  }

  if (!drake_ci_shell_exec('phploc ' . $report_options .  ' 2>&1 ' . implode(" ", $filenames))) {
    return FALSE;
  }
  $messages = drush_shell_exec_output();

  if (!$report_options) {
    // Simply pass output through.
    drush_log(implode("\n", $messages), 'status');
  }
}
