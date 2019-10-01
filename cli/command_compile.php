<?php

use \Cthulhu\lib\cli;
use \Cthulhu\lib\fmt;

function command_compile(cli\Lookup $flags, cli\Lookup $args) {
  $abspath = realpath($args->get('file'));
  if ($abspath === false) {
    fwrite(STDERR, sprintf("cannot find file: `%s`\n", $args->get('file')));
    exit(1);
  }

  $php = codegen(check(parse($abspath)));

  if ($flags->get('inline')) {
    $php = \Cthulhu\Codegen\Optimizations\Inline::apply($php);
  }

  if ($flags->get('fold')) {
    $php = \Cthulhu\Codegen\Optimizations\ConstFolding::apply($php);
  }

  if ($flags->get('tree-shake')) {
    $php = \Cthulhu\Codegen\Optimizations\TreeShaking::apply($php);
  }

  $str = $php->build()->write(new fmt\StringFormatter());
  echo $str . PHP_EOL;
}
