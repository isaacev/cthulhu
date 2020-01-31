<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class ListExpr extends Expr {
  public array $elements;

  /**
   * @param Type   $type
   * @param Expr[] $elements
   */
  public function __construct(Type $type, array $elements) {
    parent::__construct($type);
    $this->elements = $elements;
  }

  public function children(): array {
    return $this->elements;
  }

  public function from_children(array $children): ListExpr {
    return new ListExpr($this->type, $children);
  }

  public function build(): Builder {
    if (empty($this->elements)) {
      $elements = (new Builder);
    } else if (count($this->elements) === 1) {
      $elements = $this->elements[0]->build();
    } else {
      $elements = (new Builder)
        ->increase_indentation()
        ->newline()
        ->indent()
        ->each($this->elements, (new Builder)
          ->newline()
          ->indent())
        ->decrease_indentation();
    }

    return (new Builder)
      ->paren_left()
      ->keyword('list')
      ->space()
      ->paren_left()
      ->then($elements)
      ->paren_right()
      ->paren_right();
  }
}
