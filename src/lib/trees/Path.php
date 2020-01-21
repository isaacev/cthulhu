<?php

namespace Cthulhu\lib\trees;

class Path {
  public ?Path $parent;
  public Nodelike $node;

  public function __construct(?self $parent, Nodelike $node) {
    $this->parent = $parent;
    $this->node   = $node;
  }

  public function extend(Nodelike $node): self {
    return new self($this, $node);
  }
}
