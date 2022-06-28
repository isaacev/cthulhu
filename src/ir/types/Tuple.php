<?php

namespace Cthulhu\ir\types;

use Countable;

class Tuple extends ConcreteType implements Countable {
  public array $members;

  /**
   * @param Type[] $members
   */
  public function __construct(array $members) {
    assert(!empty($members));
    parent::__construct('Tuple', $members);
    $this->members = $members;
  }

  public function count(): int {
    return count($this->members);
  }

  public function fresh(ParameterContext $ctx): Type {
    $new_members = [];
    foreach ($this->members as $member) {
      $new_members[] = $member->fresh($ctx);
    }
    return new Tuple($new_members);
  }

  public function __toString(): string {
    return "(" . implode(", ", $this->members) . ")";
  }
}
