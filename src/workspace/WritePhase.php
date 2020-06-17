<?php

namespace Cthulhu\workspace;

use Cthulhu\lib\fmt\Formatter;
use Cthulhu\lib\fmt\StreamFormatter;
use Cthulhu\php\nodes\Program;
use Exception;

class WritePhase {
  private Program $php_tree;

  public function __construct(Program $php_tree) {
    $this->php_tree = $php_tree;
  }

  /**
   * @param array $args
   * @return string[]
   * @throws Exception
   */
  public function run_and_capture(array $args = []): array {
    $descriptors = [
      0 => [ 'pipe', 'r' ], // STDIN
      1 => [ 'pipe', 'w' ], // STDOUT
      2 => [ 'pipe', 'w' ], // STDERR
    ];

    $cmd  = PHP_BINARY . " -- " . implode(" ", $args);
    $proc = proc_open($cmd, $descriptors, $pipes, '', []);

    if (is_resource($proc)) {
      $this->write(new StreamFormatter($pipes[0]));
      fclose($pipes[0]);

      $stdout = stream_get_contents($pipes[1]);
      fclose($pipes[1]);

      $stderr = stream_get_contents($pipes[2]);
      fclose($pipes[2]);

      $exit_code = proc_close($proc); // TODO: check exit code

      return [ 'stdout' => $stdout, 'stderr' => $stderr ];
    } else {
      fprintf(STDERR, "unable to spawn a child process");
      exit(1);
    }
  }

  /**
   * @param array         $args
   * @param resource|null $stdout
   * @param resource|null $stderr
   */
  public function run_and_emit(array $args = [], $stdout = null, $stderr = null): void {
    $stdout = $stdout ?? STDOUT;
    $stderr = $stderr ?? STDERR;

    if (!is_resource($stdout)) {
      echo "cannot connect to STDOUT\n";
      die(1);
    } else if (!is_resource($stderr)) {
      echo "cannot connect to STDERR\n";
      die(1);
    }

    $descriptors = [
      0 => [ 'pipe', 'r' ], // STDIN
      1 => [ 'pipe', 'w' ], // STDOUT
      2 => [ 'pipe', 'w' ], // STDERR
    ];

    $cmd  = PHP_BINARY . " -- " . implode(" ", $args);
    $proc = proc_open($cmd, $descriptors, $pipes, '', []);

    if (is_resource($proc)) {
      $this->write(new StreamFormatter($pipes[0]));
      fclose($pipes[0]);

      stream_copy_to_stream($pipes[1], $stdout);
      stream_copy_to_stream($pipes[2], $stderr);

      fclose($pipes[1]);
      fclose($pipes[2]);

      exit(proc_close($proc));
    } else {
      fprintf($stderr, "unable to spawn a child process");
      exit(1);
    }
  }

  public function write(Formatter $f): Formatter {
    return $this->php_tree->build()->write($f);
  }
}
