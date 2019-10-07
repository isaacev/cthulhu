<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class UseItem extends Item {
  public $path;

  function __construct(Source\Span $span, PathNode $path, array $attrs) {
    parent::__construct($span, $attrs);
    $this->path = $path;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('UseItem', $visitor_table)) {
      $visitor_table['UseItem']($this);
    }

    $this->path->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'UseItem',
      'path' => $this->path
    ];
  }
}
