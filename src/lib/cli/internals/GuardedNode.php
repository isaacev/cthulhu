<?php

namespace Cthulhu\lib\cli\internals;

abstract class GuardedNode extends Node {
  public array $completions = [];

  /**
   * @param Node     $to_node
   * @param string[] $completions
   */
  public function __construct(Node $to_node, array $completions) {
    parent::__construct([ $to_node ]);
    $this->completions = $completions;
  }

  abstract public function matches(string $token): bool;

  /**
   * @return string[]
   */
  public function completions(): array {
    return $this->completions;
  }
}
