<?php

namespace Cthulhu\workspace;

use Cthulhu\lib\fmt\StringFormatter;
use Cthulhu\php\nodes\Program;
use Exception;

class WritePhase {
  private Program $php_tree;
  private ?string $written = null;

  public function __construct(Program $php_tree) {
    $this->php_tree = $php_tree;
  }

  /**
   * @param array $args
   * @return string[]
   * @throws Exception
   */
  public function run(array $args = []): array {
    $descriptors = [
      0 => [ 'pipe', 'r' ], // STDIN
      1 => [ 'pipe', 'w' ], // STDOUT
      2 => [ 'pipe', 'w' ], // STDERR
    ];

    $cmd  = PHP_BINARY . " -- " . implode(" ", $args);
    $proc = proc_open($cmd, $descriptors, $pipes, '', []);

    if (is_resource($proc)) {
      fwrite($pipes[0], $this->write());
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

  public function write(): string {
    if ($this->written !== null) {
      return $this->written;
    }

    return $this->written = $this->php_tree->build()->write(new StringFormatter());
  }
}
