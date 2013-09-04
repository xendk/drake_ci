<?php

/**
 * @file
 * Check coding standard using phpcs.
 *
 * Install phpcs:
 *   $ sudo pear install PHP_CodeSniffer
 * Install coder into Drush:
 *   $ cd ~/.drush && drush dl coder
 * Install Drupal Coding standard in PHPCS:
 *   $ sudo ln -sv ~/.drush/coder/coder_sniffer/Drupal $(pear config-get php_dir)/PHP/CodeSniffer/Standards/Drupal
 * (see https://drupal.org/node/1419988 for details.)
 */

/**
 * Task.
 */
$tasks['php-cs'] = array(
  'action' => 'php-cs',
  'files' => fileset('all-custom'),
  'verbose' => context_optional('verbose'),
  'output-dir' => context_optional('output-dir'),
  'standard' => context_optional('phpcs-standard'),
);

/**
 * Action.
 */
$actions['php-cs'] = array(
  'default_message' => 'PHP code sniffer',
  'callback' => 'drake_ci_php_cs',
  'parameters' => array(
    'files' => 'Files to check.',
    'verbose' => array(
      'description' => 'Print all files processed.',
      'default' => FALSE,
    ),
    'standard' => array(
      'description' => 'The coding standard files must conform to.',
      'default' => 'Drupal',
    ),
    'encoding' => array(
      'description' => 'The encoding of the files to check.',
      'default' => 'UTF8',
    ),
    'output-dir' => array(
      'description' => 'Output XML files here.',
      'default' => '',
    ),
  ),
);

/**
 * Action callback; check PHP files for coding style.
 */
function drake_ci_php_cs($context) {
  $warnings = FALSE;
  foreach ($context['files'] as $file) {
    if ($context['verbose']) {
      drush_log(dt('Code sniffing @file', array('@file' => $file->path())), 'status');
    }

    $report_options = '';
    if (!empty($context['output-dir'])) {
      $report_options = '--report-checkstyle=' . $context['output-dir'] . '/checkstyle-phpcs-' . drake_ci_flatten_path($file->path()) . '.xml';
    }
    if (!drake_ci_shell_exec('phpcs ' . $report_options . ' --standard=%s --encoding=%s 2>&1 "%s"', $context['standard'], $context['encoding'], $file)) {
      return FALSE;
    }
    $messages = drush_shell_exec_output();

    switch (drush_get_context('SHELL_RC_CODE')) {
      case 0:
        // Success, and no warnings.
        break;

      case 1:
        // Success, but with warnings.
        $warnings = TRUE;
        break;

      default:
        return drake_action_error(dt('PHPCS failed, output: @output', array('@output' => implode("\n", $messages))));
    }


    if (empty($report_options)) {
      // Get status from the 3rd last line of message
      // @fixme Too flaky assuming 3rd last line is duplication status?
      if (count($messages) < 2 || !preg_match('/^Time: (.*?) seconds, Memory: (.*?)/', $messages[count($messages) - 2])) {
        drush_log(dt('Unexpected response from phpcs: @cmd - @result',
            array(
              '@cmd' => sprintf('phpcs ' . $report_options . ' --standard=%s --encoding=%s 2>&1 "%s"', $context['standard'], $context['encoding'], $file),
              '@result' => implode("\n", $messages),
            )), 'error');
      }

      // The last two lines are irrelevant.
      $messages = array_slice($messages, 0, -2);

      // Any messages left?
      if (!empty($messages)) {
        drush_log(implode("\n", $messages), 'warning');
      }
    }
  }
  if ($warnings) {
    drush_log(dt('PHPCS found issues.'), 'warning');
  }
  else {
    drush_log(dt('PHPCS found no issues.'), 'ok');
  }
}
