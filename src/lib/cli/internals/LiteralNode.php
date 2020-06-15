<?php

namespace Cthulhu\lib\cli\internals;

class LiteralNode extends GuardedNode {
  public function matches(string $token): bool {
    return in_array($token, $this->completions);
  }
}
