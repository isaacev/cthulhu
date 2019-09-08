<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Point;
use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Source;

class File extends Node {
  public $file;
  public $items;

  function __construct(Source\File $file, array $items) {
    $span = empty($items)
      ? new Span(new Point(), new Point())
      : $items[0]->span->extended_to(end($items)->span);
    parent::__construct($span);
    $this->file = $file;
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
