<?php

/**
 * @file
 * Check CSS files using csslint.
 *
 * Install csslint:
 *   $ sudo npm install -g csslint
 */

/**
 * Task.
 */
$tasks['css-lint'] = array(
  'action' => 'css-lint',
  'files' => fileset('css-custom'),
  'verbose' => context_optional('verbose'),
  'checks' => context_optional('checks'),
  'break-on' => context_optional('break-on'),
);

/**
 * Action.
 */
$actions['css-lint'] = array(
  'default_message' => 'CSS linting.',
  'callback' => 'drake_ci_css_lint',
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
    'break-on' => array(
      'description' => 'CSSLint checks that should break the build.',
      'default' => array(
        'errors',
      ),
    ),
    /*
     * The default selection is based on discussion here:
     * http://mattwilcox.net/archive/entry/id/1054/
     */
    'checks' => array(
      'description' => 'CSSLint checks to check for.',
      'default' => array(
        'box-sizing',
        'compatible-vendor-prefixes',
        'display-property-grouping',
        'duplicate-properties',
        'empty-rules',
        'gradients',
        'import',
        'important',
        'known-properties',
        'shorthand',
        'vendor-prefix',
        'zero-units',
        'ids',
      ),
    ),
  ),
);

/**
 * Action callback; Lint CSS files.
 */
function drake_ci_css_lint($context) {
  $error = FALSE;
  if (is_array($context['break-on'])) {
    $context['break-on'] = implode(',', $context['break-on']);
  }
  if (is_array($context['checks'])) {
    $context['checks'] = implode(',', $context['checks']);
  }
  foreach ($context['files'] as $file) {
    if ($context['verbose']) {
      drush_log(dt('CSS Linting @file', array('@file' => $file->path())), 'status');
    }

    // We run csslint twice, once for build breaking errors, and once for
    // warnings. This is the easier alternative to parsing the output (which may
    // or may not be XML).

    $report_options = '--errors=' . $context['break-on'];
    if (!drake_ci_shell_exec('csslint ' . $report_options .  ' 2>&1 %s', $file->fullPath())) {
      return FALSE;
    }
    $messages = implode("\n", drush_shell_exec_output());
    if (!preg_match('/No errors in/', $messages)) {
      $error = $message;
    }

    // We include the already set --errors so the errors will show up in the
    // checkstyle*.xml file, if we're writing to one.
    $report_options .= ' --warnings=' . $context['checks'];
    if (!empty($context['output-dir'])) {
      $report_options .= ' --format=checkstyle-xml >' . $context['output-dir'] . '/checkstyle-csslint-' . drake_ci_flatten_path($file->path()) . '.xml';
    }
    else {
      $report_options .= ' 2>&1';
    }

    if (!drake_ci_shell_exec('csslint ' . $report_options .  ' %s', $file->fullPath())) {
      return FALSE;
    }

    // Only pass output through when there were warnings.
    $messages = implode("\n", drush_shell_exec_output());
    if (!preg_match('/No errors in/', $messages)) {
      if (empty($context['output-dir'])) {
        // Simply pass output through.
        drush_log($messages, 'status');
      }
    }

    if ($error) {
      drake_action_error(dt('Errors from CSSLint: @message', array('@message' => $error)));
    }
  }
}
