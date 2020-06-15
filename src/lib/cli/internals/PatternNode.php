<?php

namespace Cthulhu\lib\cli\internals;

class PatternNode extends GuardedNode {
  public string $pattern = '';

  public function __construct(Node $to_node, string $pattern) {
    parent::__construct($to_node, []);
    $this->pattern = $pattern;
  }

  public function matches(string $token): bool {
    return preg_match($this->pattern, $token);
  }
}
