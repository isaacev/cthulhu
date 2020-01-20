<?php

namespace Cthulhu\ast\nodes;

class TupleNote extends Note {
  public array $members;

  /**
   * @param Note[] $members
   */
  public function __construct(array $members) {
    parent::__construct();
    assert(count($members) > 1);
    $this->members = $members;
  }

  public function children(): array {
    return $this->members;
  }
}
