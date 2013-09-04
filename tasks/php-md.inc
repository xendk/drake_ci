<?php

/**
 * @file
 * Checks for various warning signs in PHP code using PHP-MD.
 *
 * Install phpmd:
 *   $ sudo pear channel-discover pear.phpmd.org
 *   $ sudo pear channel-discover pear.pdepend.org
 *   $ sudo pear install --alldeps phpmd/PHP_PMD
 */

/**
 * Task.
 */
$tasks['php-md'] = array(
  'action' => 'php-md',
  'files' => fileset('php-custom'),
  'verbose' => context_optional('verbose'),
  'output-dir' => context_optional('output-dir'),
);

/**
 * Action.
 */
$actions['php-md'] = array(
  'default_message' => 'PHP mess detection',
  'callback' => 'drake_ci_php_md',
  'parameters' => array(
    'files' => 'Files to check.',
    'verbose' => array(
      'description' => 'Print all files processed.',
      'default' => FALSE,
    ),
    'output-dir' => array(
      'description' => 'Output XML files here.',
      'default' => '',
    ),
  ),
);

/**
 * Action callback; check PHP files for protential problems.
 */
function drake_ci_php_md($context) {
  $warnings = FALSE;
  foreach ($context['files'] as $file) {
    if ($context['verbose']) {
      drush_log(dt('Mess detecting @file', array('@file' => $file->path())), 'status');
    }
    if (!empty($context['output-dir'])) {
      $report_options = 'xml --reportfile ' . $context['output-dir'] . '/pmd-' . drake_ci_flatten_path($file->path()) . '.xml';
    }
    else {
      $report_options = 'text';
    }

    if (!drake_ci_shell_exec('phpmd 2>&1 "%s" ' . $report_options . ' codesize,design,naming', $file)) {
      return;
    }

    $messages = drush_shell_exec_output();

    switch (drush_get_context('SHELL_RC_CODE')) {
      case 0:
        // No error.
        break;

      case 2:
        // Warning.
        $warnings = TRUE;
        break;

      default:
        // Error.
        return drake_action_error(dt('Error running phpmd: @message', array('@message' => implode("\n", $messages))));
    }

    // Remove empty lines.
    $messages = array_filter($messages);

    if (!empty($messages)) {
      foreach ($messages as $message) {
        drush_log($message, 'warning');
      }
    }
  }
  if ($warnings) {
    drush_log(dt('PHPMD found issues.'), 'warning');
  }
  else {
    drush_log(dt('PHPMD found no issues.'), 'ok');
  }
}
