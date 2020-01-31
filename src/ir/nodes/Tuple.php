<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Tuple extends Expr {
  public array $fields;

  /**
   * @param Type   $type
   * @param Expr[] $fields
   */
  public function __construct(Type $type, array $fields) {
    parent::__construct($type);
    $this->fields = $fields;
  }

  public function children(): array {
    return $this->fields;
  }

  public function from_children(array $children): EditableNodelike {
    return new self($this->type, $children);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('tuple')
      ->space()
      ->each($this->fields, (new Builder)
        ->space())
      ->paren_right();
  }
}
