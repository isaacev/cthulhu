<?php

namespace Cthulhu\lib\trees;

trait DefaultMetadata {
  protected array $metadata = [];

  /**
   * @return string[]
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
   * @param string $key
   * @param mixed  $value
   * @return self
   */
  public function set(string $key, $value): self {
    $this->metadata[$key] = $value;
    return $this;
  }

  /**
   * @param HasMetadata $other
   * @return self
   */
  public function copy(HasMetadata $other): self {
    foreach ($other->list() as $key) {
      $this->set($key, $other->get($key));
    }
    return $this;
  }
}
