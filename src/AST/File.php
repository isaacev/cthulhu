<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Point;
use Cthulhu\Parser\Lexer\Span;

class File extends Node {
  public $items;

  function __construct(array $items) {
    $span = empty($items)
      ? new Span(new Point(), new Point())
      : $items[0]->span->extended_to(end($items)->span);
    parent::__construct($span);
    $this->items = $items;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('File', $visitor_table)) {
      $visitor_table['File']($this);
    }

    foreach ($this->items as $item) {
      $item->visit($visitor_table);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'File',
      'items' => $this->items
    ];
  }
}
