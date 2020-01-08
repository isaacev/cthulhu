<?php

namespace Cthulhu\ir\names;

use Cthulhu\ir;

abstract class Symbol implements ir\HasId {
  use ir\GenerateId;

  protected array $metadata = [];

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
   * @param string $key
   * @param mixed  $value
   * @return $this
   */
  public function set(string $key, $value) {
    $this->metadata[$key] = $value;
    return $this;
  }

  public function equals(Symbol $other): bool {
    return $this->get_id() === $other->get_id();
  }

  public abstract function __toString(): string;
}
