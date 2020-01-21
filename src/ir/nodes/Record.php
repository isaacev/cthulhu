<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\hm\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Record extends Expr {
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
    return array_values($this->fields);
  }

  public function from_children(array $children): EditableNodelike {
    $fields = [];
    foreach (array_keys($this->fields) as $index => $key) {
      $fields[$key] = $children[$index];
    }
    return new self($this->type, $fields);
  }

  public function build(): Builder {
    $fields = [];
    foreach ($this->fields as $name => $expr) {
      $fields[] = (new Builder)
        ->paren_left()
        ->keyword($name)
        ->space()
        ->then($expr)
        ->paren_right();
    }

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
