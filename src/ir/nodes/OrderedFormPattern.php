<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\types\Type;

class OrderedFormPattern extends FormPattern {
  public array $order;

  /**
   * @param Type      $type
   * @param RefSymbol $ref_symbol
   * @param Pattern[] $order
   */
  public function __construct(Type $type, RefSymbol $ref_symbol, array $order) {
    parent::__construct($type, $ref_symbol);
    $this->order = $order;
  }

  public function children(): array {
    return array_values($this->order);
  }

  public function from_children(array $children): OrderedFormPattern {
    return new OrderedFormPattern($this->type, $this->ref_symbol, $children);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword("$this->ref_symbol")
      ->paren_left()
      ->each($this->order, (new Builder)
        ->keyword(',')
        ->space())
      ->paren_right();
  }

  public function __toString(): string {
    return "$this->ref_symbol";
  }
}
