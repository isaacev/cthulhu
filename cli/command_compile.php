<?php

use \Cthulhu\lib\cli;
use \Cthulhu\lib\fmt;

function command_compile(cli\Lookup $flags, cli\Lookup $args) {
  $abspath = realpath($args->get('file'));
  if ($abspath === false) {
    fwrite(STDERR, sprintf("cannot find file: `%s`\n", $args->get('file')));
    exit(1);
  }

  $optimization_passes = $flags->get_all('optimize', []);
  $all_passes = in_array('all', $optimization_passes);
  $php = codegen(check(parse($abspath)));

  if (in_array('inline', $optimization_passes) || $all_passes) {
    $php = \Cthulhu\Codegen\Optimizations\Inline::apply($php);
  }

  if (in_array('fold', $optimization_passes) || $all_passes) {
    $php = \Cthulhu\Codegen\Optimizations\ConstFolding::apply($php);
  }

  if (in_array('tree-shake', $optimization_passes) || $all_passes) {
    $php = \Cthulhu\Codegen\Optimizations\TreeShaking::apply($php);
  }

  $str = $php->build()->write(new fmt\StringFormatter());
  echo $str;
}
