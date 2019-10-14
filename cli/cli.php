<?php

ini_set('display_errors', 'stderr');

use \Cthulhu\lib\cli;

$root = (new cli\Program('cthulhu', '0.1.0'));

$root->subcommand('ast', 'Convert source code to an abstract syntax tree')
  ->single_argument('file', 'Path to the source file')
  ->callback(function (cli\Lookup $flags, cli\Lookup $args) {
    try {
      $abspath = realpath($args->get('file'));
      $workspace = (new \Cthulhu\Workspace)
        ->open($abspath ? $abspath : $args->get('file'))
        ->parse();
      echo json_encode($workspace->syntax_tree, JSON_PRETTY_PRINT) . PHP_EOL;
    } catch (\Cthulhu\Errors\Error $err) {
      $f = new \Cthulhu\lib\fmt\StreamFormatter(STDERR);
      $err->format($f);
      exit(1);
    }
  });

$root->subcommand('check', 'Check that a source file is free of errors')
  ->single_argument('file', 'Path to the source file')
  ->callback(function (cli\Lookup $flags, cli\Lookup $args) {
    try {
      $abspath = realpath($args->get('file'));
      (new \Cthulhu\Workspace)
        ->open($abspath ? $abspath : $args->get('file'))
        ->parse()
        ->link()
        ->resolve()
        ->check();

      echo "no errors in $abspath\n";
    } catch (\Cthulhu\Errors\Error $err) {
      $f = new \Cthulhu\lib\fmt\StreamFormatter(STDERR);
      $err->format($f);
      exit(1);
    }
  });

require_once __DIR__ . '/command_compile.php';
$root->subcommand('compile', 'Convert source code to PHP')
->str_flag('-o --optimize', 'Apply an optimization pass', [
  'all',
  'inline',
  'fold',
  'tree-shake'
])
  ->short_circuit_flag('--list-optimizations', 'List available optimization passes', function () {
    $passes = [
      ['all',        'Apply all optimizations'],
      ['inline',     'Replace function call sites with function body'],
      ['fold',       'Evaluate some constant expressions at compile time'],
      ['tree-shake', 'Remove function and namespace definitions that are never used']
    ];

    foreach ($passes as list($name, $desc)) {
      printf("%-16s %s\n", $name, $desc);
    }
  })
  ->single_argument('file', 'Path to the source file')
  ->callback('command_compile');

require_once __DIR__ . '/command_test.php';
$root->subcommand('test', 'Run all of the available tests')
  ->bool_flag('--bless', 'Update any stdout/stderr files for failing tests')
  ->callback('command_test');

$root->parse($argv);
