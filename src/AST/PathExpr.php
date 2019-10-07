<?php

namespace Cthulhu\AST;

class PathExpr extends Expr {
  public $path;

  function __construct(PathNode $path) {
    parent::__construct($path->span);
    $this->path = $path;
  }

  public function length(): int {
    return count($this->path->segments);
  }

  public function nth(int $n): IdentNode {
    return $this->path->segments[$n];
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('PathExpr', $visitor_table)) {
      $visitor_table['PathExpr']($this);
    }

    $this->path->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'PathExpr',
      'path' => $this->path
    ];
  }
}
