<?php

namespace Cthulhu\ir\nodes;

class FuncItem extends Item {
  public FuncHead $head;
  public Block $body;

  public function __construct(FuncHead $head, Block $body, array $attrs) {
    parent::__construct($attrs);
    $this->head = $head;
    $this->body = $body;
  }

  public function children(): array {
    return [
      $this->head,
      $this->body,
    ];
  }
}
