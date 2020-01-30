<?php

ini_set('display_errors', 'stderr');
ini_set('assert.exception', true);
assert_options(ASSERT_BAIL, 1);

use Cthulhu\lib\cli;

$root = (new cli\Program('cthulhu', '0.1.0'));

require_once __DIR__ . '/command_check.php';
$root->subcommand('check', 'Check that a source file is free of errors')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_check');

require_once __DIR__ . '/command_compile.php';
$root->subcommand('compile', 'Convert source code to PHP')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_compile');

require_once __DIR__ . '/command_test.php';
$root->subcommand('test', 'Run all of the available tests')
  ->bool_flag('--bless', 'Update any stdout/stderr files for failing tests')
  ->bool_flag('--time', 'Show how many milliseconds each test took to run')
  ->bool_flag('--eval', 'Evaluate PHP code and check that the output is expected')
  ->optional_single_argument('filter', 'Only run tests that match the filter')
  ->callback('command_test');

require_once __DIR__ . '/command_run.php';
$root->subcommand('run', 'Compile and evaluate a script')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_run');

$root->parse($argv);
