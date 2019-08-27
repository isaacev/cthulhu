<?php

require __DIR__ . '/vendor/autoload.php';

assert_options(ASSERT_BAIL, true);

if ($argc < 2) {
  echo "USAGE: php gen.php <filename>\n";
  exit(1);
}

$filename = $argv[1];
$txt = @file_get_contents($filename);
if ($txt === false) {
  echo "unable to contents of '$filename'\n";
  exit(1);
}

$ast = \Cthulhu\Parser\Parser2::from_string($txt)->file();
$mod = \Cthulhu\Analysis\Analyzer2::file($ast);

echo json_encode($mod, JSON_PRETTY_PRINT) . "\n";
