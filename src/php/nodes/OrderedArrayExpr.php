<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class OrderedArrayExpr extends Expr {
  public array $elements;

  /**
   * @param Expr[] $elements
   */
  function __construct(array $elements) {
    parent::__construct();
    $this->elements = $elements;
  }

  public function to_children(): array {
    return $this->elements;
  }

  public function from_children(array $nodes): Node {
    return new self($nodes);
  }

  public function build(): Builder {
    if (empty($this->elements)) {
      return (new Builder)
        ->bracket_left()
        ->bracket_right();
    } else if (count($this->elements) === 1) {
      return (new Builder)
        ->bracket_left()
        ->space()
        ->then($this->elements[0])
        ->space()
        ->bracket_right();
    } else {
      return (new Builder)
        ->bracket_left()
        ->increase_indentation()
        ->newline_then_indent()
        ->each($this->elements, (new Builder)
          ->comma()
          ->newline_then_indent())
        ->decrease_indentation()
        ->newline_then_indent()
        ->bracket_right();
    }
  }
}
