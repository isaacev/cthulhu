<?php

use Cthulhu\err\Error;
use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\LoadPhase;

function command_check(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $relpath = $args->get('file');
    $abspath = realpath($relpath);
    $checked = LoadPhase::from_filepath($abspath ? $abspath : $relpath)
      ->check();

    if ($flags->get('ir', false)) {
      $checked
        ->optimize()
        ->ir()
        ->build()
        ->write(new StreamFormatter(STDOUT))
        ->newline();
    } else {
      echo "no errors in $abspath\n";
    }
  } catch (Error $err) {
    $f = new StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}
