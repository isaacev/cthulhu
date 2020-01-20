<?php

namespace Cthulhu\ast;

class Trie {
  /* @var mixed|null $value */
  public $value = null;

  /* @var self[] $table */
  private array $table = [];

  public function frontier_contains(string $char): bool {
    assert(strlen($char) === 1);
    return array_key_exists($char, $this->table);
  }

  public function next(string $char): ?self {
    if ($this->frontier_contains($char)) {
      return $this->table[$char];
    }
    return null;
  }

  public function next_or_create(string $char): self {
    if ($next = $this->next($char)) {
      return $next;
    }
    return $this->table[$char] = new self();
  }

  /**
   * @param mixed $value
   */
  public function write($value): void {
    $this->value = $value;
  }

  /**
   * @param string $full
   * @param mixed  $value
   */
  public function write_or_create(string $full, $value): void {
    if (strlen($full) === 0) {
      $this->write($value);
    } else {
      $head = $full[0];
      $tail = substr($full, 1);
      $this->next_or_create($head)->write_or_create($tail, $value);
    }
  }
}
