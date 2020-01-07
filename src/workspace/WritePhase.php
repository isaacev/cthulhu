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
   * @return string
   * @throws Exception
   */
  public function run(): string {
    $descriptors = [
      0 => [ 'pipe', 'r' ], // STDIN
      1 => [ 'pipe', 'w' ], // STDOUT
      2 => [ 'pipe', 'w' ], // STDERR
    ];

    $proc = proc_open(PHP_BINARY, $descriptors, $pipes, '', []);

    if (is_resource($proc)) {
      fwrite($pipes[0], $this->write());
      fclose($pipes[0]);

      $stdout = stream_get_contents($pipes[1]);
      fclose($pipes[1]);

      $stderr = stream_get_contents($pipes[2]);
      fclose($pipes[2]);

      $exit_code = proc_close($proc);

      if (!empty($stderr)) {
        throw new Exception($stderr);
      } else if ($exit_code !== 0) {
        throw new Exception("finished with non-zero exit code: $exit_code");
      }

      return $stdout;
    } else {
      throw new Exception("unable to spawn a child process");
    }
  }

  public function write(): string {
    if ($this->written !== null) {
      return $this->written;
    }

    return $this->written = $this->php_tree->build()->write(new StringFormatter());
  }
}
