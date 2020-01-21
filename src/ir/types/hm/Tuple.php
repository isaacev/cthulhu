<?php

namespace Cthulhu\ir\types\hm;

class Tuple extends TypeOper {
  public array $fields;

  /**
   * @param Type[] $fields
   */
  public function __construct(array $fields) {
    assert(!empty($fields));
    parent::__construct('Tuple', $fields);
    $this->fields = $fields;
  }

  public function __toString(): string {
    return "(" . implode(", ", $this->fields) . ")";
  }
}
