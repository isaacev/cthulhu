<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class File extends Node {
  public $file;
  public $items;

  function __construct(Source\File $file, array $items) {
    $span = empty($items)
      ? new Source\Span(new Source\Point($file), new Source\Point($file))
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
