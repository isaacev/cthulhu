<?php

use Cthulhu\err\Error;
use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\LoadPhase;

/** @noinspection PhpUnusedParameterInspection */
function command_compile(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $filepath = $args->get('file');
    echo LoadPhase::from_filepath($filepath)
      ->check()
      ->optimize()
      ->codegen()
      ->optimize()
      ->write();
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr());
    exit(1);
  }
}
