<?php

namespace Cthulhu\AST;

class PathExpr extends Expr {
  public $segments;

  function __construct(array $segments) {
    $span = $segments[0]->span->extended_to(end($segments)->span);
    parent::__construct($span);
    $this->segments = $segments;
  }

  public function length(): int {
    return count($this->segments);
  }

  public function nth(int $n): IdentNode {
    return $this->segments[$n];
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('PathExpr', $visitor_table)) {
      $visitor_table['PathExpr']($this);
    }

    foreach ($this->segments as $segment) {
      $segment->visit($visitor_table);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'PathExpr',
      'segments' => array_map(function ($s) { return $s->jsonSerialize(); }, $this->segments)
    ];
  }
}
