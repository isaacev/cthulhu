<?php

use Cthulhu\lib\cli;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\workspace\ReadPhase;

function command_run(cli\Lookup $flags, cli\Lookup $args) {
  try {
    $relpath = $args->get('file');
    $abspath = realpath($relpath);
    $php_src = ReadPhase::from_file_system($abspath ? $abspath : $relpath)
      ->parse()
      ->link()
      ->resolve()
      ->check()
      ->codegen()
      ->optimize([])
      ->write();

    $descriptors = [
      0 => [ 'pipe', 'r' ], // STDIN
      1 => [ 'pipe', 'w' ], // STDOUT
      2 => [ 'pipe', 'w' ], // STDERR
    ];

    $proc = proc_open('php', $descriptors, $pipes, '', []);

    if (is_resource($proc)) {
      fwrite($pipes[0], $php_src);
      fclose($pipes[0]);

      echo stream_get_contents($pipes[1]);
      fclose($pipes[1]);
      fclose($pipes[2]);

      exit(proc_close($proc));
    } else {
      fwrite(STDERR, 'unable to spawn a php process');
      exit(1);
    }
  } catch (\Cthulhu\Errors\Error $err) {
    $f = new StreamFormatter(STDERR);
    $err->format($f);
    exit(1);
  }
}
