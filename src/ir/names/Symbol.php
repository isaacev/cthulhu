<?php

namespace Cthulhu\ir\names;

use Cthulhu\ir;
use Cthulhu\lib\trees\DefaultUniqueId;

abstract class Symbol implements ir\HasId {
  use DefaultUniqueId;

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

  abstract public function __toString(): string;
}
