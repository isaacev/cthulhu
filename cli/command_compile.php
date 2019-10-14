<?php

use \Cthulhu\lib\cli;

function command_compile(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $abspath = realpath($args->get('file'));
    $passes = $flags->get_all('optimize', []);
    echo (new \Cthulhu\Workspace)
      ->open($abspath ? $abspath : $args->get('file'))
      ->parse()
      ->link()
      ->resolve()
      ->check()
      ->codegen()
      ->optimize([
        // 'all'        => in_array('all', $passes),
        // 'inline'     => in_array('inline', $passes),
        // 'fold'       => in_array('fold', $passes),
        // 'tree-shake' => in_array('tree-shake', $passes),
      ])
      ->write();
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new \Cthulhu\lib\fmt\StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}
