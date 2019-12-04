<?php

ini_set('display_errors', 'stderr');
assert_options(ASSERT_BAIL, 1);

use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\ReadPhase;

$root = (new cli\Program('cthulhu', '0.1.0'));

$root->subcommand('check', 'Check that a source file is free of errors')
  ->single_argument('file', 'Path to the source file')
  ->callback(function (cli\Lookup $flags, cli\Lookup $args) {
    try {
      $relpath = $args->get('file');
      $abspath = realpath($relpath);
      ReadPhase::from_file_system($abspath ? $abspath : $relpath)
        ->parse()
        ->link()
        ->resolve()
        ->check();

      echo "no errors in $abspath\n";
    } catch (\Cthulhu\Errors\Error $err) {
      $f = new StreamFormatter(STDERR);
      $err->format($f);
      exit(1);
    }
  });

require_once __DIR__ . '/command_compile.php';
$root->subcommand('compile', 'Convert source code to PHP')
  ->str_flag('-o --optimize', 'Apply an optimization pass', 'pass', [
    'all',
    'inline',
    'fold',
    'tree-shake',
  ])
  ->short_circuit_flag('--list-optimizations', 'List available optimization passes', function () {
    $passes = [
      [ 'all', 'Apply all optimizations' ],
      [ 'inline', 'Replace function call sites with function body' ],
      [ 'fold', 'Evaluate some constant expressions at compile time' ],
      [ 'tree-shake', 'Remove function and namespace definitions that are never used' ],
    ];

    foreach ($passes as [$name, $desc]) {
      printf("%-16s %s\n", $name, $desc);
    }
  })
  ->single_argument('file', 'Path to the source file')
  ->callback('command_compile');

require_once __DIR__ . '/command_test.php';
$root->subcommand('test', 'Run all of the available tests')
  ->bool_flag('--bless', 'Update any stdout/stderr files for failing tests')
  ->bool_flag('--time', 'Show how many milliseconds each test took to run')
  ->optional_single_argument('filter', 'Only run tests that match the filter')
  ->callback('command_test');

require_once __DIR__ . '/command_run.php';
$root->subcommand('run', 'Compile and evaluate a script')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_run');

$root->parse($argv);
