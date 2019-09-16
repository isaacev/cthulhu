<?php

use \Cthulhu\utils\cli;

require_once __DIR__ . '/command_test.php';

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
    $f = new \Cthulhu\utils\fmt\StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}

function check(\Cthulhu\AST\File $ast): \Cthulhu\IR\SourceModule {
  try {
    return \Cthulhu\Analysis\Analyzer::ast_to_module($ast);
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new \Cthulhu\utils\fmt\StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}

function codegen(\Cthulhu\IR\SourceModule $module): \Cthulhu\Codegen\PHP\Program {
  try {
    $bootstrap = \Cthulhu\Codegen\PHP\Reference::from_symbol($module->scope->to_symbol('main'));
    return \Cthulhu\Codegen\Codegen::generate($module, $bootstrap);
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new \Cthulhu\utils\fmt\StreamFormatter(STDERR);
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

$root->subcommand('compile', 'Convert source code to PHP')
  ->single_argument('file', 'Path to the source file')
  ->callback(function (cli\Lookup $flags, cli\Lookup $args) {
    $abspath = realpath($args->get('file'));
    if ($abspath === false) {
      fwrite(STDERR, sprintf("cannot find file: `%s`\n", $args->get('file')));
      exit(1);
    }

    $php = codegen(check(parse($abspath)));
    $str = $php->build()->write(new \Cthulhu\Codegen\StringWriter());
    echo $str . PHP_EOL;
  });

$root->subcommand('test', 'Run all of the available tests')
  ->bool_flag('bless', 'Update any stdout/stderr files for failing tests')
  ->callback('command_test');

$root->parse($argv);
