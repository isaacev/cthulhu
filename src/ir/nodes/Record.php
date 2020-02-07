<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class Record extends Expr {
  public array $fields;

  /**
   * @param Type    $type
   * @param Field[] $fields
   */
  public function __construct(Type $type, array $fields) {
    parent::__construct($type);
    $this->fields = $fields;
  }

  public function children(): array {
    return $this->fields;
  }

  public function from_children(array $children): Record {
    return new Record($this->type, $children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('record')
      ->space()
      ->increase_indentation()
      ->newline()
      ->indent()
      ->each($fields, (new Builder)
        ->newline()
        ->indent())
      ->decrease_indentation()
      ->paren_right();
  }
}
