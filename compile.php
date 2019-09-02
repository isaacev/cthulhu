#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

if ($argc < 2) {
  echo 'USAGE: ./compile <filename>' . PHP_EOL;
  exit(1);
}

$intput_filename = $argv[1];
$basename = basename($intput_filename);
$txt = @file_get_contents($intput_filename);
if ($txt === false) {
  echo "unable to get contents of '$intput_filename'" . PHP_EOL;
  exit(1);
}

try {
  $ref = new \Cthulhu\Codegen\PHP\Reference([ $basename, 'main' ]);
  $ast = \Cthulhu\Parser\Parser::from_string($txt)->file();
  $mod = \Cthulhu\Analysis\Analyzer::file($basename, $ast);
} catch (\Cthulhu\Errors\Error $err) {
  echo $err;
  exit(1);
} catch (\Exception $ex) {
  echo "$ex\n";
  exit(2);
}

$php = \Cthulhu\Codegen\Codegen::generate($mod, $ref);
$str = $php->build()->write(new \Cthulhu\Codegen\StringWriter());

$output_filename = $basename . '.php';
$output_file = fopen($output_filename, 'w') or die('unable to open file');
fwrite($output_file, $str);
fclose($output_file);

echo "compiled $intput_filename to $output_filename" . PHP_EOL;
