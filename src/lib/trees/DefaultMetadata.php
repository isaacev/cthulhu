<?php

namespace Cthulhu\lib\trees;

trait DefaultMetadata {
  protected array $metadata = [];

  /**
   * @return array
   */
  public function list(): array {
    return array_keys($this->metadata);
  }

  public function has(string $key): bool {
    return array_key_exists($key, $this->metadata);
  }

  /**
   * @param string $key
   * @return mixed|null
   */
  public function get(string $key) {
    return $this->has($key) ? $this->metadata[$key] : null;
  }

  /**
   * @param string $head
   * @param string ...$rest
   * @return mixed|null
   */
  public function chain(string $head, string ...$rest) {
    $curr  = $this;
    $total = 1 + count($rest);
    foreach ([ $head, ...$rest ] as $index => $key) {
      $is_last = $index === $total - 1;
      $next    = $curr->get($key);
      if ($is_last) {
        return $next;
      } else if ($curr->get($key) instanceof HasMetadata) {
        $curr = $curr->get($key);
        continue;
      } else {
        break;
      }
    }
    return null;
  }

  /**
   * @param string $key
   * @param mixed  $value
   * @return $this
   */
  public function set(string $key, $value): self {
    $this->metadata[$key] = $value;
    return $this;
  }

  /**
   * @param HasMetadata $other
   * @return $this
   */
  public function copy(HasMetadata $other): self {
    foreach ($other->list() as $key) {
      $this->set($key, $other->get($key));
    }
    return $this;
  }
}
