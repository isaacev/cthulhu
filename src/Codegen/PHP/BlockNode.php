<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class BlockNode extends Node {
  public $stmts;

  function __construct(array $stmts) {
    $this->stmts = $stmts;
  }

  public function visit(array $table): void {
    parent::visit($table);
    if (array_key_exists('Block', $table)) {
      $table['Block']($this);
    }

    foreach ($this->stmts as $stmt) { $stmt->visit($table); }
  }

  public function is_empty(): bool {
    return count($this->stmts) === 0;
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
