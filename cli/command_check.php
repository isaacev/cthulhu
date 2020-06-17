<?php

use Cthulhu\err\Error;
use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\LoadPhase;

function command_check(cli\Lookup $options, cli\Lookup $flags, cli\Lookup $args) {
  $use_color = $options->get('color');
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
        ->write(StreamFormatter::stdout($use_color))
        ->newline();
    } else {
      StreamFormatter::stdout($use_color)
        ->printf("no errors in %s", $abspath)
        ->newline();
    }
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr($use_color));
    exit(1);
  }
}
