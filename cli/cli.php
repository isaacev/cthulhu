<?php

ini_set('display_errors', 'stderr');

use \Cthulhu\lib\cli;

function parse(string $absolute_path): \Cthulhu\AST\File {
  $contents = @file_get_contents($absolute_path);
  if ($contents === false) {
    fwrite(STDERR, sprintf("cannot read file: `%s`\n", $absolute_path));
    exit(1);
  }

  try {
    $file = new \Cthulhu\Source\File($absolute_path, $contents);
    $ast = \Cthulhu\Parser\Parser::file_to_ast($file);
    return $ast;
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new \Cthulhu\lib\fmt\StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}

function check(\Cthulhu\AST\File $ast): \Cthulhu\IR\Program {
  try {
    return \Cthulhu\Analysis\Analyzer::ast_to_program($ast);
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new \Cthulhu\lib\fmt\StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}

function codegen(\Cthulhu\IR\Program $prog): \Cthulhu\Codegen\PHP\Program {
  try {
    return \Cthulhu\Codegen\Codegen::generate($prog);
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new \Cthulhu\lib\fmt\StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}

$root = (new cli\Program('cthulhu', '0.1.0'));

$root->subcommand('ast', 'Convert source code to an abstract syntax tree')
  ->single_argument('file', 'Path to the source file')
  ->callback(function (cli\Lookup $flags, cli\Lookup $args) {
    $abspath = realpath($args->get('file'));
    if ($abspath === false) {
      fwrite(STDERR, sprintf("cannot find file: `%s`\n", $args->get('file')));
      exit(1);
    }

    $ast = parse($abspath);
    echo json_encode($ast, JSON_PRETTY_PRINT) . PHP_EOL;
  });

$root->subcommand('check', 'Check that a source file is free of errors')
  ->single_argument('file', 'Path to the source file')
  ->callback(function (cli\Lookup $flags, cli\Lookup $args) {
    $abspath = realpath($args->get('file'));
    if ($abspath === false) {
      fwrite(STDERR, sprintf("cannot find file: `%s`\n", $args->get('file')));
      exit(1);
    }

    check(parse($abspath));
    echo "no errors in $abspath\n";
  });

require_once __DIR__ . '/command_compile.php';
$root->subcommand('compile', 'Convert source code to PHP')
  ->bool_flag('inline', 'Optimize program by inlining some function calls')
  ->bool_flag('fold', 'Perform basic constant folding')
  ->single_argument('file', 'Path to the source file')
  ->callback('command_compile');

require_once __DIR__ . '/command_test.php';
$root->subcommand('test', 'Run all of the available tests')
  ->bool_flag('bless', 'Update any stdout/stderr files for failing tests')
  ->callback('command_test');

$root->parse($argv);
