<?php

use Cthulhu\err\Error;
use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\LoadPhase;

function command_run(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $filepath = $args->get('file');
    echo LoadPhase::from_filepath($filepath)
      ->check()
      ->optimize()
      ->codegen()
      ->optimize()
      ->run($args->get('args'));
  } catch (Error $err) {
    $err->format(StreamFormatter::stderr());
    exit(1);
  } catch (Exception $ex) {
    fwrite(STDERR, "$ex");
    exit(1);
  }
}
