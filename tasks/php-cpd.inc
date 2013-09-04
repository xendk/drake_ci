<?php

/**
 * @file
 * Detects duplicate PHP code.
 *
 * Install phpcpd:
 *   $ sudo pear channel-discover pear.phpunit.de
 *   $ sudo pear channel-discover pear.netpirates.net
 *   $ sudo pear install --alldeps phpunit/phpcpd
 */

/**
 * Task
 */
$tasks['php-cpd'] = array(
  'action' => 'php-cpd',
  'files' => fileset('php-custom'),
  'output-dir' => context_optional('output-dir'),
);

/**
 * Action.
 */
$actions['php-cpd'] = array(
  'default_message' => 'PHP copy/paste detection',
  'callback' => 'drake_ci_php_cpd',
  'parameters' => array(
    'files' => 'Files to check.',
    'output-dir' => array(
      'description' => 'Output XML files here.',
      'default' => '',
    ),
  ),
);


/**
 * Action callback; check PHP files for duplicate code.
 */
function drake_ci_php_cpd($context) {
  $filenames = array();
  foreach ($context['files'] as $file) {
    $filenames[] = drush_escapeshellarg($file->fullPath());
  }

  $report_options = '';
  if (!empty($context['output-dir'])) {
    $report_options = '--log-pmd ' . $context['output-dir'] . '/cpd.xml';
  }

  if (!drake_ci_shell_exec('phpcpd ' . $report_options.  ' 2>&1 ' . implode(" ", $filenames))) {
    return FALSE;
  }
  $messages = drush_shell_exec_output();

  if (!$report_options) {
    // Get status from the 3rd last line of message
    // @fixme Too flaky assuming 3rd last line is duplication status?
    if (count($messages) < 5 || !preg_match('/^(\d+\.\d+)\% duplicated/', $messages[count($messages) - 3], $matches)) {
      drush_log(dt('Unexpected response from phpcpd: @cmd - @result',
          array(
            '@cmd' => sprintf('phpcpd 2>&1 "%s"', $file),
            '@result' => implode("\n", $messages),
          )), 'error');
    }

    // The first and last two lines are irrelevant.
    $messages = array_slice($messages, 2, -2);

    // Higher than 0% duplication?
    if ($matches[1] > 0) {
      foreach ($messages as $message) {
        drush_log($message, 'warning');
      }
    }
  }

  // In reality phpcpd returns 1 on both duplication found and file not found,
  // so we can't really be sure what non-zero means.
  if (drush_get_context('SHELL_RC_CODE') != 0) {
    drush_log(dt('PHPCPD found issues.'), 'warning');
  }
  else {
    drush_log(dt('PHPCPD found no issues.'), 'ok');
  }
}
