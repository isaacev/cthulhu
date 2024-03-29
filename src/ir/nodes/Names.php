<?php

namespace Cthulhu\ir\nodes;

use Countable;

class Names extends Node implements Countable {
  public array $names;

  /**
   * @param Name[] $names
   */
  public function __construct(array $names) {
    parent::__construct();
    $this->names = $names;
  }

  public function count(): int {
    return count($this->names);
  }

  public function get_name(int $i): Name {
    return $this->names[$i];
  }

  public function children(): array {
    return $this->names;
  }

  public function from_children(array $children): Names {
    return (new Names($children))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->each($this->names, (new Builder)
        ->space())
      ->paren_right();
  }
}
