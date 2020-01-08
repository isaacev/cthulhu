<?php

namespace Cthulhu\lib\cli\internals;

class Scanner {
  protected array $argv = [];
  protected int $index = 0;

  public function __construct(array $argv) {
    $this->argv = $argv;
  }

  public function is_empty(): bool {
    return $this->index >= count($this->argv);
  }

  public function not_empty(): bool {
    return $this->is_empty() === false;
  }

  public function next_starts_with(string $prefix): bool {
    if ($this->is_empty()) {
      return false;
    }
    return $prefix === substr($this->argv[$this->index], 0, strlen($prefix));
  }

  public function next_is(string $pattern): bool {
    return !!(preg_match($pattern, $this->argv[$this->index]));
  }

  public function advance(): ?string {
    if ($this->is_empty()) {
      return null;
    }
    return $this->argv[$this->index++];
  }

  public static function fatal_error(string $format, ...$args): void {
    fwrite(STDERR, sprintf($format, ...$args) . PHP_EOL);
    exit(1);
  }
}
