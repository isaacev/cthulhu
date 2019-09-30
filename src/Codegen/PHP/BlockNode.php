<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class BlockNode extends Node {
  public $stmts;

  function __construct(array $stmts) {
    $this->stmts = $stmts;
  }

  public function is_empty(): bool {
    return count($this->stmts) === 0;
  }

  public function to_children(): array {
    return $this->stmts;
  }

  public function from_children(array $nodes): Node {
    return new self($nodes);
  }

  public function length(): int {
    return count($this->stmts);
  }

  public function build(): Builder {
    $is_empty = (new Builder)
      ->comment('empty');

    $not_empty = (new Builder)
      ->stmts($this->stmts);

    return (new Builder)
      ->brace_left()
      ->increase_indentation()
      ->newline_then_indent()
      ->choose($this->is_empty(), $is_empty, $not_empty)
      ->decrease_indentation()
      ->newline_then_indent()
      ->brace_right();
  }

  public function jsonSerialize() {
    return [
      'type' => 'BlockNode'
    ];
  }
}
