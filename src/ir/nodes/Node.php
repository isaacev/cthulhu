<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir;

abstract class Node implements ir\HasId {
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
  public function set(string $key, $value): self {
    $this->metadata[$key] = $value;
    return $this;
  }

  public abstract function children(): array;
}
