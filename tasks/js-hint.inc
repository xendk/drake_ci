<?php

/**
 * @file
 * Check JavaScript files using js-hint.
 *
 * Install jshint:
 *   $ sudo npm install -g jshint
 */

/**
 * Task.
 */
$tasks['js-hint'] = array(
  'action' => 'js-hint',
  'files' => fileset('js-custom'),
  'verbose' => context_optional('verbose'),
  'output-dir' => context_optional('output-dir'),
);

/**
 * Action.
 */
$actions['js-hint'] = array(
  'default_message' => 'JSHinting files',
  'callback' => 'drake_ci_js_hint',
  'parameters' => array(
    'files' => 'Files to lint.',
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
 * Action callback; Check JS files for syntax errors.
 */
function drake_ci_js_hint($context) {
  $warnings = FALSE;
  foreach ($context['files'] as $file) {
    if ($context['verbose']) {
      drush_log(dt('JSHinting  @file', array('@file' => $file->path())), 'status');
    }
    if (!empty($context['output-dir'])) {
      $report_options = '--reporter checkstyle >' . $context['output-dir'] . '/checkstyle-jshint-' . drake_ci_flatten_path($file->path()) . '.xml';
    }
    else {
      $report_options = '--show-non-errors';
    }

    if (!drake_ci_shell_exec('jshint 2>&1 ' . $report_options . ' "%s"', $file)) {
      return FALSE;
    }

    if (empty($context['output-dir'])) {
      $messages = drush_shell_exec_output();
      drush_log(implode("\n", $messages), $status);

      switch (drush_get_error('SHELL_RC_CODE')) {
        case 0:
          drush_log(implode("\n", $messages), 'ok');
          break;

        case 2:
          $warnings = TRUE;
          drush_log(implode("\n", $messages), 'warning');
          break;

        default:
          return drake_action_error(dt('Error running jshint, message: @message', array('@message' => implode("\n", $messages))));
          break;
      }
    }
  }
  if ($warnings) {
    drake_action_error(dt('Errors found in JS files.'));
  }
}
