<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir;

abstract class Node implements ir\HasId {
  use ir\GenerateId;

  protected array $metadata = [];

  function has(string $key): bool {
    return array_key_exists($key, $this->metadata);
  }

  /**
   * @param string $key
   * @return mixed|null
   */
  function get(string $key) {
    return $this->has($key) ? $this->metadata[$key] : null;
  }

  /**
   * @param string $key
   * @param mixed  $value
   * @return $this
   */
  function set(string $key, $value): self {
    $this->metadata[$key] = $value;
    return $this;
  }

  abstract function children(): array;
}
