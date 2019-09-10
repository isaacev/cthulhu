<?php

require_once __DIR__ . '/args.php';

function parse(string $absolute_path): \Cthulhu\AST\File {
  $contents = @file_get_contents($absolute_path);
  if ($contents === false) {
    CLI\fatal('cannot read %s', $absolute_path);
  }

  try {
    $file = new \Cthulhu\Source\File($absolute_path, $contents);
    $ast = \Cthulhu\Parser\Parser::file_to_ast($file);
    return $ast;
  } catch (\Cthulhu\Errors\Error $err) {
    $tty = new \Cthulhu\Debug\TeletypeStream(STDERR, [ 'color' => posix_isatty(STDERR) ]);
    $err->format($tty);
    exit(1);
  }
}

function check(\Cthulhu\AST\File $ast): \Cthulhu\IR\SourceModule {
  try {
    return \Cthulhu\Analysis\Analyzer::ast_to_module($ast);
  } catch (\Cthulhu\Errors\Error $err) {
    $tty = new \Cthulhu\Debug\TeletypeStream(STDERR, [ 'color' => posix_isatty(STDERR) ]);
    $err->format($tty);
    exit(1);
  }
}

function codegen(\Cthulhu\IR\SourceModule $module): \Cthulhu\Codegen\PHP\Program {
  try {
    $bootstrap = \Cthulhu\Codegen\PHP\Reference::from_symbol($module->scope->to_symbol('main'));
    return \Cthulhu\Codegen\Codegen::generate($module, $bootstrap);
  } catch (\Cthulhu\Errors\Error $err) {
    $tty = new \Cthulhu\Debug\TeletypeStream(STDERR, [ 'color' => posix_isatty(STDERR) ]);
    $err->format($tty);
    exit(1);
  }
}

$ast = (new CLI\CommandBuilder('ast'))
  ->argument('file')
  ->callback(function (string $input) {
    $absolute_filepath = realpath($input);
    if ($absolute_filepath === false) {
      CLI\fatal('cannot find %s', $input);
    } else {
      $ast = parse($absolute_filepath);
      echo json_encode($ast, JSON_PRETTY_PRINT) . PHP_EOL;
    }
  });

$check = (new CLI\CommandBuilder('check'))
  ->argument('file')
  ->callback(function (string $input) {
    $absolute_filepath = realpath($input);
    if ($absolute_filepath === false) {
      CLI\fatal('cannot find %s', $input);
    } else {
      check(parse($absolute_filepath));
      echo "no errors\n";
      exit(0);
    }
  });

$codegen = (new CLI\CommandBuilder('codegen'))
  ->argument('file')
  ->callback(function (string $input) {
    $absolute_filepath = realpath($input);
    if ($absolute_filepath === false) {
      CLI\fatal('cannot find %s', $input);
    } else {
      $php = codegen(check(parse($absolute_filepath)));
      $str = $php->build()->write(new \Cthulhu\Codegen\StringWriter());
      echo $str . PHP_EOL;
    }
  });

(new CLI\Parser)
  ->command($ast)
  ->command($check)
  ->command($codegen)
  ->dispatch($argv);
