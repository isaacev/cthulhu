<?php

use \Cthulhu\lib\cli;

function command_compile(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $abspath = realpath($args->get('file'));
    echo (new \Cthulhu\Workspace)
      ->open($abspath ? $abspath : $args->get('file'))
      ->parse()
      ->link()
      ->resolve()
      ->check()
      ->codegen()
      ->write();
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new \Cthulhu\lib\fmt\StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}
