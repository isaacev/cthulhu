<?php

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 'stderr');
ini_set('assert.exception', true);
ini_set('log_errors', 1);
ini_set('display_errors', 0);
assert_options(ASSERT_BAIL, 1);

use Cthulhu\err\Error;
use Cthulhu\lib\cli;
use Cthulhu\lib\debug\Debug;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\lib\test;
use Cthulhu\workspace\LoadPhase;

/**
 * Command line grammar
 *
 * The CLI is built using a custom parsing library. In addition to providing
 * methods for describing the interface and parsing a complete command, it also
 * exposes a tab-completion API to support a richer experience when using the
 * CLI in a shell.
 */

$root = (new cli\Program('cthulhu', '0.1.0'))
  ->bool_flag('--debug', 'Output extra diagnostics about compiler internals')
  ->inverse_bool_flag('--no-color', 'Suppress use of ANSI colors in output');

$root->subcommand('check', 'Check that a source file is free of errors')
  ->bool_flag('--ir', 'Print intermediate representation')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_check');

$root->subcommand('compile', 'Convert source code to PHP')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_compile');

$root->subcommand('test', 'Run all of the available tests')
  ->bool_flag('--list', 'List all available tests without running any tests')
  ->bool_flag('--bless', 'Update any stdout/stderr files for failing tests')
  ->bool_flag('--verbose', 'Show each test by name including timing info')
  ->bool_flag('--eval', 'Evaluate PHP code and check that the output is expected')
  ->variadic_argument('filters', 'Only run tests that match one of the filters')
  ->callback('command_test');

$root->subcommand('run', 'Compile and evaluate a script')
  ->single_argument('file', 'Path to the source file')
  ->variadic_argument('args', 'Arguments for the script')
  ->callback('command_run');

$root->parse($argv);

function command_check(cli\Lookup $options, cli\Lookup $flags, cli\Lookup $args) {
  Debug::setup($options);

  $use_color = $options->get('color');
  try {
    $relpath = $args->get('file');
    $abspath = realpath($relpath);
    $checked = LoadPhase::from_filepath($abspath ? $abspath : $relpath)
      ->check();

    if ($flags->get('ir', false)) {
      $checked
        ->optimize()
        ->ir()
        ->build()
        ->write(StreamFormatter::stdout($use_color))
        ->newline();
    } else {
      StreamFormatter::stdout($use_color)
        ->printf("no errors in %s", $abspath)
        ->newline();
    }
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr($use_color));
    exit(1);
  }
}

/** @noinspection PhpUnusedParameterInspection */
function command_compile(cli\Lookup $options, cli\Lookup $flags, cli\Lookup $args) {
  Debug::setup($options);

  $use_color = $options->get('color');
  try {
    $filepath = $args->get('file');
    LoadPhase::from_filepath($filepath)
      ->check()
      ->optimize()
      ->codegen()
      ->optimize()
      ->write(StreamFormatter::stdout($use_color));
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr($use_color));
    exit(1);
  }
}

function command_test(cli\Lookup $options, cli\Lookup $flags, cli\Lookup $args) {
  Debug::setup($options);

  $use_color   = $options->get('color');
  $is_blessed  = $flags->get('bless', false);
  $is_verbose  = $flags->get('verbose', false);
  $list_only   = $flags->get('list', false);
  $do_php_eval = $flags->get('eval', false);
  $filters     = $args->get('filters', []);
  $tests       = test\Runner::find_tests();
  $stdout      = StreamFormatter::stdout($use_color);

  if ($list_only === true) {
    foreach ($tests as $index => $test) {
      if (!empty($filters) && !$test->name_matches_one_of($filters)) {
        continue;
      }

      $stdout
        ->print($test->group_and_name())
        ->newline();
    }
    exit(0);
  }

  $replacements = [
    realpath(test\Runner::DEFAULT_DIR) => 'TEST_DIR',
    realpath(test\Runner::STDLIB_DIR) => 'STDLIB_DIR',
  ];

  $reporter = $is_verbose
    ? new test\VerboseTestReporter(count($tests), $stdout)
    : new test\SimpleTestReporter(count($tests), $stdout);

  $reporter->on_start();
  foreach ($tests as $index => $test) {
    if (!empty($filters) && !$test->name_matches_one_of($filters)) {
      $reporter->on_skip($test);
    } else {
      $reporter->on_pre_run($test);

      $result = $test->run($do_php_eval, $replacements);
      if ($is_blessed && $result instanceof test\TestFailed) {
        $test->bless($result->found);
      }

      if ($result instanceof test\TestPassed) {
        $reporter->on_pass($result);
      } else if ($is_blessed) {
        $reporter->on_pass(new test\TestPassed($test, $result->buildtime, $result->runtime));
      } else {
        $reporter->on_fail($result);
      }
    }
  }

  $reporter->on_diff();

  $reporter->on_stats();

  exit($reporter->count_failed() ? 1 : 0);
}

/** @noinspection PhpUnusedParameterInspection */
function command_run(cli\Lookup $options, cli\Lookup $flags, cli\Lookup $args) {
  Debug::setup($options);

  $use_color = $options->get('color');
  try {
    $filepath = $args->get('file');
    LoadPhase::from_filepath($filepath)
      ->check()
      ->optimize()
      ->codegen()
      ->optimize()
      ->run_and_emit($args->get('args'));
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr($use_color));
    exit(1);
  } catch (Exception $ex) {
    fwrite(STDERR, "$ex");
    exit(1);
  }
}
