<?php

use Cthulhu\err\Error;
use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\LoadPhase;

/** @noinspection PhpUnusedParameterInspection */
function command_run(cli\Lookup $options, cli\Lookup $flags, cli\Lookup $args) {
  $use_color = $options->get('color');
  try {
    $filepath = $args->get('file');
    LoadPhase::from_filepath($filepath)
      ->check()
      ->optimize()
      ->codegen()
      ->optimize()
      ->run_and_emit($args->get('args'));
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr($use_color));
    exit(1);
  } catch (Exception $ex) {
    fwrite(STDERR, "$ex");
    exit(1);
  }
}
