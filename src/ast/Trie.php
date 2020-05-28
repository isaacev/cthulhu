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
  protected function insert($value): void {
    $this->value = $value;
  }

  /**
   * If a key exists in the trie, update its value.
   * If no such key exists, insert the value with the key.
   *
   * @param string $key
   * @param mixed  $value
   */
  public function upsert(string $key, $value): void {
    if (strlen($key) === 0) {
      $this->insert($value);
    } else {
      $head = $key[0];
      $tail = substr($key, 1);
      $this->next_or_create($head)->upsert($tail, $value);
    }
  }
}
