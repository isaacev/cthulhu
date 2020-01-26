<?php

namespace Cthulhu\lib\trees;

class Path {
  public ?Path $parent;
  public Nodelike $node;
  public string $kind;

  public function __construct(?self $parent, Nodelike $node) {
    $this->parent = $parent;
    $this->node   = $node;
    $this->kind   = self::get_kind($node);
  }

  public function extend(Nodelike $node): self {
    return new self($this, $node);
  }

  public static function get_kind(Nodelike $node): string {
    return CallbackTable::get_node_kinds($node)[0];
  }
}
