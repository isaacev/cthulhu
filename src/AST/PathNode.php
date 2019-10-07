<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class PathNode extends Node {
  public $extern;
  public $segments;

  function __construct(Source\Span $span, bool $extern, array $segments) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->segments = $segments;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('PathNode', $visitor_table)) {
      $visitor_table['PathNode']($this);
    }

    foreach ($this->segments as $segment) {
      $segment->visit($visitor_table);
    }
  }

  public function __toString(): string {
    return ($this->extern ? '::' : '') . implode('::', $this->segments);
  }

  public function jsonSerialize() {
    return [
      'type' => 'PathNode',
      'extern' => $this->extern,
      'segments' => $this->segments
    ];
  }
}
