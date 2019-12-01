<?php


namespace Cthulhu\php\nodes;


use Cthulhu\php\Builder;

class AssociativeArrayExpr extends Expr {
  public array $fields;

  /**
   * @param FieldNode[] $fields
   */
  function __construct(array $fields) {
    parent::__construct();
    $this->fields = $fields;
  }

  function to_children(): array {
    return $this->fields;
  }

  function from_children(array $nodes): Node {
    return new self($nodes);
  }

  function build(): Builder {
    if (empty($this->fields)) {
      return (new Builder)
        ->bracket_left()
        ->bracket_right();
    }

    return (new Builder)
      ->bracket_left()
      ->increase_indentation()
      ->newline_then_indent()
      ->each($this->fields, (new Builder)
        ->comma()
        ->newline_then_indent())
      ->decrease_indentation()
      ->newline_then_indent()
      ->bracket_right();
  }
}
