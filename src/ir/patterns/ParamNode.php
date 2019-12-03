<?php

namespace Cthulhu\ir\patterns;

class ParamNode extends Node {
  protected bool $has_wildcard = false;

  function is_covered(): bool {
    return $this->has_wildcard;
  }

  function is_redundant(Pattern $pattern): bool {
    return $this->is_covered();
  }

  public function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
    } else {
      assert(false, 'unreachable');
    }
  }

  public function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    } else {
      return [ new WildcardPattern() ];
    }
  }
}
