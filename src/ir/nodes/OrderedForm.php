<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class OrderedForm extends Form {
  public array $order;

  /**
   * @param Name   $name
   * @param Type[] $order
   */
  public function __construct(Name $name, array $order) {
    parent::__construct($name);
    $this->order = $order;
  }

  public function children(): array {
    return [ $this->name ];
  }

  public function from_children(array $children): OrderedForm {
    return new OrderedForm($children[0], $this->order);
  }

  public function build(): Builder {
    $order = (new Builder);
    foreach ($this->order as $index => $type) {
      if ($index > 0) {
        $order->space();
      }
      $order->type($type);
    }

    return (new Builder)
      ->paren_left()
      ->keyword('form')
      ->space()
      ->then($this->name)
      ->paren_left()
      ->then($order)
      ->paren_right()
      ->paren_right();
  }
}
