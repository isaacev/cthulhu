<?php

use Cthulhu\err\Error;
use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\LoadPhase;

/** @noinspection PhpUnusedParameterInspection */
function command_compile(cli\Lookup $options, cli\Lookup $flags, cli\Lookup $args) {
  $use_color = $options->get('color');
  try {
    $filepath = $args->get('file');
    LoadPhase::from_filepath($filepath)
      ->check()
      ->optimize()
      ->codegen()
      ->optimize()
      ->write(StreamFormatter::stdout($use_color));
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr($use_color));
    exit(1);
  }
}
