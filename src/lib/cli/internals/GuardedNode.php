<?php

namespace Cthulhu\lib\cli\internals;

abstract class GuardedNode extends Node {
  public array $completions = [];

  public function __construct(Node $to_node, array $completions) {
    parent::__construct([ $to_node ]);
    $this->completions = $completions;
  }

  abstract public function matches(string $token): bool;

  public function completions(): array {
    return $this->completions;
  }
}
