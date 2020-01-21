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
   * @param string $head
   * @param string ...$rest
   * @return mixed
   */
  public function chain(string $head, string ...$rest);

  /**
   * @param string $key
   * @param mixed  $value
   * @return self
   */
  public function set(string $key, $value): self;

  /**
   * @param HasMetadata $other
   * @return $this
   */
  public function copy(HasMetadata $other): self;
}
