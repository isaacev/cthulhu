<?php

namespace Cthulhu\ir;

class Path {
  public ?Path $parent;
  public nodes\Node $node;

  function __construct(?self $parent, nodes\Node $node) {
    $this->parent = $parent;
    $this->node   = $node;
  }

  function extend(nodes\Node $node): self {
    return new self($this, $node);
  }
}
