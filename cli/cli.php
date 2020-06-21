<?php

ini_set('display_errors', 'stderr');
ini_set('assert.exception', true);
assert_options(ASSERT_BAIL, 1);

use Cthulhu\lib\cli;

$root = (new cli\Program('cthulhu', '0.1.0'))
  ->inverse_bool_flag('--no-color', 'Suppress use of ANSI colors in output');

require_once __DIR__ . '/command_check.php';
$root->subcommand('check', 'Check that a source file is free of errors')
  ->bool_flag('--ir', 'Print intermediate representation')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_check');

require_once __DIR__ . '/command_compile.php';
$root->subcommand('compile', 'Convert source code to PHP')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_compile');

require_once __DIR__ . '/command_test.php';
$root->subcommand('test', 'Run all of the available tests')
  ->bool_flag('--list', 'List all available tests without running any tests')
  ->bool_flag('--bless', 'Update any stdout/stderr files for failing tests')
  ->bool_flag('--verbose', 'Show each test by name including timing info')
  ->bool_flag('--eval', 'Evaluate PHP code and check that the output is expected')
  ->variadic_argument('filters', 'Only run tests that match one of the filters')
  ->callback('command_test');

require_once __DIR__ . '/command_run.php';
$root->subcommand('run', 'Compile and evaluate a script')
  ->single_argument('file', 'Path to the source file')
  ->variadic_argument('args', 'Arguments for the script')
  ->callback('command_run');

$root->parse($argv);
