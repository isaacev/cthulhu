<?php

namespace Cthulhu\loc;

class Directory {
  /**
   * Absolute path to the directory WITHOUT a trailing slash.
   */
  public string $path;

  /**
   * Paths marked as 'internal' are allowed to link to the `::Kernel` module.
   * Currently, only the stdlib directory is considered internal.
   * @var bool
   */
  public bool $is_internal;

  public function __construct(string $path, bool $is_internal) {
    $this->path        = $path;
    $this->is_internal = $is_internal;
  }

  /**
   * @return Filepath[]
   */
  public function scan(): array {
    $found = [];

    foreach (scandir($this->path) as $filename) {
      if ($filename === '.' || $filename === '..') {
        continue;
      }

      $filepath = Filepath::from_directory($this, $filename);
      if (is_file("$filepath")) {
        $found[] = $filepath;
      }
    }

    return $found;
  }

  public function __toString(): string {
    return $this->path;
  }

  public static function stdlib(): self {
    return new self(realpath(__DIR__ . '/../stdlib'), true);
  }
}
