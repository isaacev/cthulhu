<?php

namespace Cthulhu\lib\cli\internals;

class Node {
  public array $to_nodes = [];

  public function __construct(array $to_nodes) {
    $this->to_nodes = $to_nodes;
  }

  public function find_guarded(): array {
    $guarded = [];
    foreach ($this->to_nodes as $node) {
      if ($node instanceof GuardedNode) {
        $guarded[] = $node;
      } else {
        $guarded = array_merge($guarded, $node->find_guarded());
      }
    }
    return $guarded;
  }
}
