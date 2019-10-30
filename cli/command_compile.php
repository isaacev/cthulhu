<?php

use \Cthulhu\lib\cli;
use \Cthulhu\lib\fmt\StreamFormatter;
use \Cthulhu\workspace\ReadPhase;

function command_compile(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $relpath = $args->get('file');
    $abspath = realpath($relpath);
    $passes = $flags->get_all('optimize', []);
    echo ReadPhase::from_file_system($abspath ? $abspath : $relpath)
      ->parse()
      ->link()
      ->resolve()
      ->check()
      ->codegen()
      ->optimize([
        'all'        => in_array('all', $passes),
        'inline'     => in_array('inline', $passes),
        'fold'       => in_array('fold', $passes),
        'tree-shake' => in_array('tree-shake', $passes),
      ])
      ->write();
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}
