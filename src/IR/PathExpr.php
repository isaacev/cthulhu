<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

class PathExpr extends Expr {
  public $segments;
  public $type;

  function __construct(array $segments, Types\Type $type) {
    $this->segments = $segments;
    $this->type = $type;
  }

  public function type(): Types\Type {
    return $this->type;
  }

  public function jsonSerialize() {
    return [
      'type' => 'PathExpr',
      'segments' => array_map(function ($s) {
        return $s->jsonSerialize();
      }, $this->segments)
    ];
  }
}
