<?php

use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\ReadPhase;

function command_run(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $relpath = $args->get('file');
    $abspath = realpath($relpath);
    echo ReadPhase::from_file_system($abspath ? $abspath : $relpath)
      ->parse()
      ->link()
      ->resolve()
      ->check()
      ->codegen()
      ->optimize([])
      ->run();
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  } catch (\Exception $ex) {
    fwrite(STDERR, "$ex");
    exit(1);
  }
}
