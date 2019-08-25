<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class ModuleStmt extends Stmt {
  public $name;
  public $block;

  function __construct(Span $span, IdentNode $name, BlockNode $block) {
    parent::__construct($span);
    $this->name = $name;
    $this->block = $block;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('ModuleStmt', $visitor_table)) {
      $visitor_table['ModuleStmt']($this);
    }

    $this->name->visit($visitor_table);
    $this->block->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'ModuleStmt',
      'name' => $this->name->jsonSerialize(),
      'block' => $this->block->jsonSerialize()
    ];
  }
}
