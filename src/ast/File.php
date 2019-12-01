<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class File extends Node {
  public Source\File $file;
  public array $items;

  /**
   * @param Source\File $file
   * @param Item[] $items
   */
  function __construct(Source\File $file, array $items) {
    $span = empty($items)
      ? new Source\Span(new Source\Point($file), new Source\Point($file))
      : $items[0]->span->extended_to(end($items)->span);
    parent::__construct($span);
    $this->file = $file;
    $this->items = $items;
  }
}
