<?php

ini_set('display_errors', 'stderr');
assert_options(ASSERT_BAIL, 1);

use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\LoadPhase;

$root = (new cli\Program('cthulhu', '0.1.0'));

$root->subcommand('check', 'Check that a source file is free of errors')
  ->single_argument('file', 'Path to the source file')
  ->callback(function (cli\Lookup $flags, cli\Lookup $args) {
    try {
      $relpath = $args->get('file');
      $abspath = realpath($relpath);
      LoadPhase::from_filepath($abspath ? $abspath : $relpath)
        ->check();

      echo "no errors in $abspath\n";
    } catch (\Cthulhu\err\Error $err) {
      $f = new StreamFormatter(STDERR);
      $err->format($f);
      exit(1);
    }
  });

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
