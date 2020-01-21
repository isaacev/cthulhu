<?php

namespace Cthulhu\lib\trees;

interface HasMetadata {
  /**
   * @return string[]
   */
  public function list(): array;

  public function has(string $key): bool;

  /**
   * @param string $key
   * @return mixed|null
   */
  public function get(string $key);

  /**
   * @param string $key
   * @param mixed  $value
   * @return self
   */
  public function set(string $key, $value);

  /**
   * @param HasMetadata $other
   * @return self
   */
  public function copy(HasMetadata $other);
}
