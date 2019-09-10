#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

if ($argc < 2) {
  echo 'USAGE: ./compile <filename>' . PHP_EOL;
  exit(1);
}

$input_filename = $argv[1];
$txt = @file_get_contents($input_filename);
if ($txt === false) {
  echo "unable to get contents of '$input_filename'" . PHP_EOL;
  exit(1);
}

try {
  $file = new \Cthulhu\Source\File($input_filename, $txt);
  $ast  = \Cthulhu\Parser\Parser::file_to_ast($file);
  $mod  = \Cthulhu\Analysis\Analyzer::ast_to_module($ast);
  $ref  = \Cthulhu\Codegen\PHP\Reference::from_symbol($mod->scope->to_symbol('main'));
} catch (\Cthulhu\Errors\Error $err) {
  echo $err;
  exit(1);
} catch (\Exception $ex) {
  echo "$ex\n";
  exit(2);
}

$php = \Cthulhu\Codegen\Codegen::generate($mod, $ref);
$str = $php->build()->write(new \Cthulhu\Codegen\StringWriter());

$output_filename = $file->basename() . '.php';
$output_file = fopen($output_filename, 'w') or die('unable to open file');
fwrite($output_file, $str);
fclose($output_file);

echo "compiled $input_filename to $output_filename" . PHP_EOL;
