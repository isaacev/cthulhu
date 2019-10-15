<?php

namespace Cthulhu\ast;

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
}
