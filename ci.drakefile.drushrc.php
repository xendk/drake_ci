<?php

/**
 * @file
 * Generic code checking/testing and analysis tasks.
 */

$api = 1;

/*
 * Default context for tasks.
 */
$context = array(
  'root' => context('@self:site:root'),
);

/*
 * Filesets for the tasks.
 */
$filesets['php'] = array(
  'include' => array(
    '*.php',
    '*.module',
    '*.install',
    '*.inc',
    '*.profile',
    '*.test',
  ),
);

$filesets['php-generated'] = array(
  'include' => array(
    '**/*.features.*',
    '**/*.field_group.inc',
    '**/*.layouts.inc',
    '**/*.pages_default.inc',
    '**/*.panels_default.inc',
    '**/*.panelizer.inc',
    '**/*.strongarm.inc',
    '**/*.views_default.inc',
  ),
);

$filesets['contrib'] = array(
  'include' => array(
    '**/contrib/**',
    '**/libraries/**',
  ),
);

$filesets['core'] = array(
  // These patterns are anchored at /.
  'include' => array(
    // Matches files in the Drupal root dir.
    '/*',
    '/includes/**',
    '/misc/**',
    '/scripts/**',
    '/modules/**',
    '/themes/**',
    '/sites/*',
    '/sites/default/**',
    '/sites/*/settings.php',
    '/profiles/minimal/**',
    '/profiles/standard/**',
    '/profiles/testing/**',
    '/profiles/default/**',
  ),
);

$filesets['js'] = array(
  'include' => array('*.js'),
  // Minimized JavaScript files should not be analyzed.  In their optimized
  // state they can not be expected to conform to coding standards.
  'exclude' => array('*.min.js'),
);

$filesets['css'] = array(
  'include' => array('*.css'),
);

/*
 * Convinience filesets.
 *
 * Default commands use these, but they can be extended or redifined as the user
 * sees fit.
 */
$filesets['php-custom'] = array(
  'dir' => context('root'),
  'extend' => array(
    'php',
    'no-php-generated',
    'no-core',
    'no-contrib',
  ),
);

$filesets['js-custom'] = array(
  'dir' => context('root'),
  'extend' => array(
    'js',
    'no-core',
    'no-contrib',
  ),
);

$filesets['css-custom'] = array(
  'dir' => context('root'),
  'extend' => array(
    'css',
    'no-core',
    'no-contrib',
  ),
);

$filesets['all-custom'] = array(
  'dir' => context('root'),
  'extend' => array(
    'php',
    'no-php-generated',
    'css',
    'js',
    'no-core',
    'no-contrib',
  ),
);

/*
 * Convinience tasks.
 *
 * Default implementation of all actions so that site drakefiles doesn't have to
 * implement them. They can of course be overwritten.
 */
$tasks['ci-clean'] = array(
  'action' => 'ci-clean',
  'output-dir' => context_optional('output-dir'),
);

$tasks['check-all'] = array(
  'depends' => array(
    'check-php',
    'check-js',
    'run-tests',
  ),
);

$tasks['check-php'] = array(
  'depends' => array(
    'ci-clean',
    'php-lint',
    'php-debug',
    'php-cs',
    'php-md',
    'php-cpd',
    'php-loc',
  ),
);

$tasks['check-js'] = array(
  'depends' => array(
    'ci-clean',
    'js-hint',
    'js-debug',
  ),
);

$tasks['check-css'] = array(
  'depends' => array(
    'ci-clean',
    'css-lint',
  ),
);

$tasks['run-tests'] = array(
  // Might seem overkill with a task with only one dependency, but
  // behat/selenium/other tests could be added.
  'depends' => array(
    'run-simpletests',
  ),
);

/*
 * Check PHP syntax. Only requies the php cli command.
 */
$tasks['php-lint'] = array(
  'action' => 'php-lint',
  'files' => fileset('php-custom'),
  'verbose' => context_optional('verbose'),
);

/*
 * Uses grep to check for certain debugging statements.
 */
$tasks['php-debug'] = array(
  'action' => 'php-debug',
  'files' => fileset('php-custom'),
  'verbose' => context_optional('verbose'),
);

/*
 * Checks for various warning signs in PHP code using PHP-MD.
 *
 * Install phpmd:
 *   $ sudo pear channel-discover pear.phpmd.org
 *   $ sudo pear channel-discover pear.pdepend.org
 *   $ sudo pear install --alldeps phpmd/PHP_PMD
*/
$tasks['php-md'] = array(
  'action' => 'php-md',
  'files' => fileset('php-custom'),
  'verbose' => context_optional('verbose'),
  'output-dir' => context_optional('output-dir'),
);

/*
 * Detects duplicate PHP code.
 *
 * Install phpcpd:
 *   $ sudo pear channel-discover pear.phpunit.de
 *   $ sudo pear channel-discover pear.netpirates.net
 *   $ sudo pear install --alldeps phpunit/phpcpd
 */
$tasks['php-cpd'] = array(
  'action' => 'php-cpd',
  'files' => fileset('php-custom'),
  'output-dir' => context_optional('output-dir'),
);

/*
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
$tasks['php-cs'] = array(
  'action' => 'php-cs',
  'files' => fileset('all-custom'),
  'verbose' => context_optional('verbose'),
  'output-dir' => context_optional('output-dir'),
  'standard' => context_optional('phpcs-standard'),
);

/*
 * Analyse PHP code for size and structure using phploc..
 *
 * Install phploc:
 *   $ sudo pear channel-discover pear.phpunit.de
 *   $ sudo pear install --alldeps phpunit/phploc
*/
$tasks['php-loc'] = array(
  'action' => 'php-loc',
  'files' => fileset('php-custom'),
  'output-dir' => context_optional('output-dir'),
);

/*
 * Check JavaScript files using js-hint.
 *
 * Install jshint:
 *   $ sudo npm install -g jshint
 */
$tasks['js-hint'] = array(
  'action' => 'js-hint',
  'files' => fileset('js-custom'),
  'verbose' => context_optional('verbose'),
  'output-dir' => context_optional('output-dir'),
);

/*
 * Check JS files for debug statements, using grep.
 */
$tasks['js-debug'] = array(
  'action' => 'js-debug',
  'files' => fileset('js-custom'),
  'verbose' => context_optional('verbose'),
);

/*
 * Check CSS files using csslint.
 *
 * Install csslint:
 *   $ sudo npm install -g csslint
 */
$tasks['css-lint'] = array(
  'action' => 'css-lint',
  'files' => fileset('css-custom'),
  'verbose' => context_optional('verbose'),
  'checks' => context_optional('checks'),
  'break-on' => context_optional('break-on'),
);

/*
 * Run simpletests from the specified files.
 */
$tasks['run-simpletests'] = array(
  'action' => 'run-simpletests',
  'files' => fileset('php-custom'),
  'output-dir' => context_optional('output-dir'),
  'port' => context_optional('port'),
  'no-cleanup' => context_optional('no-cleanup'),
  'tests' => context_optional('tests'),
  'abort-on-failure' => context_optional('abort-on-failure'),
);

/*
 * Clean up output directory, if specified.
 */
$actions['ci-clean'] = array(
  'default_message' => 'Setting up/cleaning output directories.',
  'callback' => 'drake_ci_clean',
  'parameters' => array(
    'output-dir' => array(
      'description' => 'Directory for results of analysis.',
      'default' => '',
    ),
  ),
);

/**
 * CI clean action. Empty the output directory, if specified.
 */
function drake_ci_clean($context) {
  if (!empty($context['output-dir'])) {
    if (!file_exists(dirname($context['output-dir']))) {
      return drake_action_error(dt('Parent dir to output-dir does not exist.'));

    }
    if (file_exists($context['output-dir'])) {
      drush_delete_dir($context['output-dir']);
    }
    mkdir($context['output-dir']);
    if (!is_writable($context['output-dir'])) {
      return drake_action_error(dt('Error setting up output-dir.'));
    }
  }
}

/**
 * It's here things get interesting, here the actual testing actions are
 * defined.
 */

/**
 * PHP lint action. Runs the files through PHP to check for syntax errors.
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


/**
 * PHP debug statement check action. Greps files for common debug statements.
 */
$actions['php-debug'] = array(
  'default_message' => 'Checking PHP files for debug statements',
  'callback' => 'drake_ci_php_debug',
  'parameters' => array(
    'files' => 'Files to check for debug statements.',
    'verbose' => array(
      'description' => 'Print all files processed.',
      'default' => FALSE,
    ),
  ),
);

/**
 * Action callback; check files for debugging statements.
 */
function drake_ci_php_debug($context) {
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

/**
 * JSHint action. Runs the files through jshint to check for syntax and style
 * errors.
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


/**
 * JS debug statement check action. Greps files for common debug statements.
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

/**
 * PHPMD action. Runs the files through PHPMD to check for potential problems.
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

/**
 * PHPCPD action. Runs the files through PHPCPD to check for duplicate code.
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

/**
 * PHPCS action. Runs the files through PHPCS to check coding style.
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

/**
 * PHPLOC action. Runs the files through PHPLOC to analyse code.
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

/**
 * CSSLint action. Runs the files through CSSLint to check for errors.
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

/**
 * Simpletest action. Runs simpletests from the specified files.
 */
$actions['run-simpletests'] = array(
  'default_message' => 'Simpletests.',
  'callback' => 'drake_ci_run_simpletests',
  'parameters' => array(
    'files' => 'Files to check for tests.',
    'output-dir' => array(
      'description' => 'Output CSV files here.',
      'default' => '',
    ),
    'port' => array(
      'description' => 'Port number to use for local server.',
      'default' => '',
    ),
    'no-cleanup' => array(
      'description' => 'Do not remove installed test site.',
      'default' => '',
    ),
    'tests' => array(
      'description' => 'Specific tests to run. If not specified, tests found in files will be run.',
      'default' => array(),
    ),
    'abort-on-failure' => array(
      'description' => 'Whether to abort on first failure.',
      'default' => FALSE,
    ),
  ),
);


/**
 * Action callback; run simpletests for files.
 */
function drake_ci_run_simpletests($context) {
  // If port was not specified, pick a random port. With so many to choose from,
  // we're unlikely to collide.
  $port = !empty($context['port']) ? $context['port'] : mt_rand(50000, 60000);
  $cleanup = !((bool) $context['no-cleanup']);
  $site_dir = $port . '.localhost';
  $profile = NULL;
  $tests = $context['tests'];
  if (!is_array($tests)) {
    $tests = explode(",", $tests);
  }
  $default_options = array(
    'uri' => 'http://localhost:' . $port,
  );

  // We need to figure out whether any af the tests we'll run is from a module
  // that's in a profile, as we need to install the site with that profile to
  // run the given test. However, we can't figure out precisely what tests we'll
  // run, as we need an installation to verify that it's an test we'll really
  // run.
  //
  // This is "good enough", it'll simply pick a profile if we found any tests
  // that was located in a profile. It'll fail if it finds tests in multiple
  // profiles, but that's very unlikely to happen in real usage anyway, and then
  // the user will just have to be more explicit.
  $potential_tests = array();
  if (empty($tests)) {
    // Find tests to run by greping all selected files for class names, and
    // taking all that's also listed in the drush test-run listing.
    foreach ($context['files'] as $file) {
      // Only bother with test files.
      // @todo will need to be adjusted for D8.
      if (preg_match('/.test$/', $file->path())) {
        drush_shell_exec('grep "class " %s', $file->fullPath());
        // Grep returns all lines that contains "class ", filter it down a bit and
        // get the class names.
        if (preg_match_all('/^\s*(?:abstract|final)?\s*class\s+(\S+)[^{]*{/m', implode("\n", drush_shell_exec_output()), $matches, PREG_PATTERN_ORDER)) {
          foreach ($matches[1] as $name) {
            // Figure out if we need a specific profile for the tests.
            if (preg_match('{^profiles/([^/]+)/}', $file->path(), $m)) {
              if (!empty($profile) and $profile != $m[1]) {
                return drake_action_error('Cannot test files from different profiles.');
              }
              $profile = $m[1];
            }
            // Create a lookup table.
            $potential_tests[$name] = TRUE;
          }
        }
      }
    }

    if (empty($potential_tests)) {
      // No tests specified and none found. Return.
      drush_log(dt('No tests found in files, skipping.'), 'status');
      return;
    }
  }

  // Use the minimal profile if no specific profile is required.
  if (empty($profile)) {
    $profile = 'minimal';
  }

  // Register dir/files for deletion when we're done.
  if ($cleanup) {
    // Delete site dir.
    drush_register_file_for_deletion('sites/' . $site_dir, TRUE);
    // Delete database.
    drush_register_file_for_deletion('sites/' . $site_dir . '.sqlite', TRUE);
  }

  // If the site dir exists, assume we don't need to set it up.
  if (!file_exists('sites/' . $site_dir)) {
    $args = array($profile);
    $options = array(
      // Drupal does not like it when the database is in the site dir, because
      // the installer changes the permissions so SQLite can't create a lock
      // file which makes it fail. So we'll put it outside.
      'db-url' => 'sqlite://sites/' . $site_dir . '.sqlite',
      'sites-subdir' => $site_dir,
    );
    $res = drush_invoke_process(NULL, 'site-install', $args, $options, TRUE);

    if (!$res || $res['error_status'] != 0) {
      return drake_action_error(dt('Error installing site.'));
    }

    // Enable simpletest.module.
    $res = drush_invoke_process(NULL, 'pm-enable', array('simpletest'), $default_options, TRUE);

    if (!$res || $res['error_status'] != 0) {
      return drake_action_error(dt('Error enabling simpletest module.'));
    }
  }

  // Use a temporary log file, to avoid buffers being filled.
  $log_file = '/tmp/simpletest-' . $port . '-' . posix_getpid() . '.log';
  drush_register_file_for_deletion($log_file);
  $descriptorspec = array(
    0 => array('file', '/dev/null', 'r'),
    1 => array('file', '/dev/null', 'w'),
    2 => array('file', $log_file, 'w'),
  );
  // We'd like to use drush runserver instead, but in initial testing runserver
  // would cause core tests to fail on login, while this would not.
  $process = proc_open('php -S localhost:' . $port . ' ' . dirname(__FILE__) . '/router.php', $descriptorspec, $pipes);
  if (!$process) {
    return drake_action_error(dt('Could not start internal web server..'));
  }

  // Register a shutdown function to properly close the subprocess.
  register_shutdown_function('_drake_ci_run_simpletests_shutdown', $process);

  // Figure out which of the potential test names is available as tests that can
  // be run.
  if (count($potential_tests)) {
    // Get list of all tests.
    $res = drush_invoke_process(NULL, 'test-run', array(), $default_options + array('pipe' => TRUE), TRUE);

    if (!$res || $res['error_status'] != 0) {
      return drake_action_error(dt('Error getting list af all tests.'));
    }
    // You'd think that using --pipe would be da shizzle, but it's just an
    // array represenation of the table printed, including headers, group
    // headers and formatting spaces.
    foreach ($res['object']['_data'] as $row) {
      $class_name = trim($row[0]);
      if (isset($potential_tests[$class_name])) {
        $tests[] = $class_name;
      }
    }
  }

  if (empty($tests)) {
    // No tests to run, exit.
    drush_log(dt('No runnable tests found, skipping.'), 'status');
  }

  $test_errors = FALSE;
  // Run the tests.
  $options = $default_options;
  if (!empty($context['output-dir'])) {
    $dir = $context['output-dir'] . '/xUnit';
    $options += array('xml' => $dir);
    // Clean task should ensure that the output-dir exists.
    if (!file_exists($dir)) {
      mkdir($dir);
    }
  }
  foreach ($tests as $test) {
    $res = drush_invoke_process(NULL, 'test-run', array($test), $options, TRUE);

    if (!$res || $res['error_status'] != 0) {
      $test_errors = TRUE;
      if ($context['abort-on-failure']) {
        return drake_action_error(dt('Error while running test @test, aborting', array('@test' => $test)));
      }
      drush_log(dt('Error while running test @test', array('@test' => $test)), 'error');
    }
  }

  if ($test_errors) {
    // Messages was already logged.
    return FALSE;
  }
}

/**
 * Shutdown function to end the PHP server process.
 */
function _drake_ci_run_simpletests_shutdown($process) {
  // We assume that all is dandy if the server is still running. Can't count on
  // return code from proc_close.
  $proc_status = proc_get_status($process);
  $php_rc = $proc_status["running"] ? 0 : $proc_status["exitcode"];
  if ($php_rc != 0) {
    drush_set_error('PHP_SERVER_ERROR', dt("The PHP server process returned error."));
  }

  if ($proc_status['running'] && $proc_status['pid']) {
    // In some PHP versions, the child process isn't php, but sh. Try to find
    // the child processes of the process and kill them off by hand first.
    $ppid = $proc_status['pid'];
    $pids = array();
    // Get any children of the sub process by asking ps for a list of processes
    // with their parent pid and looking through it for our subprocess. On Linux
    // the right options to ps can give just the children, but this has the
    // advantage of also working on OSX.
    drush_shell_exec("ps -o pid,ppid -ax");
    foreach (drush_shell_exec_output() as $line) {
      if (preg_match('/^\s*([0-9]+)\s*' . $proc_status['pid'] . '\s*$/', $line, $m)) {
        $pids[] = $m[1];
      }
    }
    foreach ($pids as $pid) {
      posix_kill($pid, 2);
    }
    // Terminate the main child process.
    proc_terminate($process);
  }
  proc_close($process);
}

/**
 * Execute a command that might use a non-zero exit code.
 *
 * This works much like drush_shell_exec(), but only returns FALSE if the
 * command couldn't be run. If the command returns a non-zero exit code, this
 * will still return TRUE, unlike drush_shell_exec().
 *
 * The return code of the command is saved to the SHELL_RC_CODE context for
 * inspection.
 */
function drake_ci_shell_exec($cmd) {
  $args = func_get_args();
  // Do not change the command itself, just the parameters.
  for ($x = 1; $x < count($args); $x++) {
    $args[$x] = drush_escapeshellarg($args[$x]);
  }
  // Important: we allow $args to take one of two forms here.  If
  // there is only one item in the array, it is the already-escaped
  // command string, but otherwise sprintf is used.  In the case
  // of pre-escaped strings, sprintf will fail if any of the escaped
  // parameters contain '%', so we must not call sprintf unless necessary.
  if (count($args) == 1) {
    $command = $args[0];
  }
  else {
    $command = call_user_func_array('sprintf', $args);
  }

  if (drush_get_context('DRUSH_VERBOSE') || drush_get_context('DRUSH_SIMULATE')) {
    drush_print('Executing: ' . $command, 0, STDERR);
  }
  if (!drush_get_context('DRUSH_SIMULATE')) {
    exec($command . ' 2>&1', $output, $result);
    _drush_shell_exec_output_set($output);

    if (drush_get_context('DRUSH_DEBUG')) {
      foreach ($output as $line) {
        drush_print($line, 2);
      }
    }

    // The sh command returns 127 when it couldn't find the command.
    if ($result == 127) {
      $tmp = explode(' ', $cmd);
      return drake_action_error(dt('Error running @command: @message', array(
            '@message' => implode("\n", drush_shell_exec_output()),
            '@command' => trim($tmp[0]))));
    }
    // Save the return code in context.
    drush_set_context('SHELL_RC_CODE', $result);
    // Return true.
    return TRUE;
  }
  else {
    return TRUE;
  }
}

/**
 * Flatten a path to a file name.
 *
 * Flattens path/to/file-name.php to path-to-file--name.php
 */
function drake_ci_flatten_path($path) {
  return strtr($path, array('-' => '--', '/' => '-'));
}
