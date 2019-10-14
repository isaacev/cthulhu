<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class CompoundPathNode extends Node {
  public $extern;
  public $body;
  public $tail;

  function __construct(Source\Span $span, bool $extern, array $body, $tail) {
    parent::__construct($span);
    $this->extern = $extern;
    $this->body = $body;
    $this->tail = $tail;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('CompoundPathNode', $visitor_table)) {
      $visitor_table['CompoundPathNode']($this);
    }

    foreach ($this->body as $segment) {
      $segment->visit($visitor_table);
    }
    $this->tail->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'CompoundPathNode',
      'extern' => $this->extern,
      'body' => $this->body,
      'tail' => $this->tail,
    ];
  }
}
