<?php

namespace Cthulhu\lib\cli\internals;

class Scanner {
  protected $argv = [];
  protected $index = 0;

  function __construct(array $argv) {
    $this->argv = $argv;
  }

  function is_empty(): bool {
    return $this->index >= count($this->argv);
  }

  function not_empty(): bool {
    return $this->is_empty() === false;
  }

  function next_starts_with(string $prefix): bool {
    if ($this->is_empty()) {
      return false;
    }
    return $prefix === substr($this->argv[$this->index], 0, strlen($prefix));
  }

  function next_is(string $pattern): bool {
    return !!(preg_match($pattern, $this->argv[$this->index]));
  }

  function advance(): ?string {
    if ($this->is_empty()) {
      return null;
    }
    return $this->argv[$this->index++];
  }

  static function fatal_error(string $format, ...$args): void {
    fwrite(STDERR, sprintf($format, ...$args) . PHP_EOL);
    exit(1);
  }
}
