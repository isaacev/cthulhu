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
        ->write(StreamFormatter::stdout())
        ->newline();
    } else {
      echo "no errors in $abspath\n";
    }
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr());
    exit(1);
  }
}
