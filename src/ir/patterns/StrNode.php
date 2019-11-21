<?php

namespace Cthulhu\ir\patterns;

class StrNode extends Node {
  protected $has_wildcard = false;
  protected $has_values = [];

  function is_covered(): bool {
    return $this->has_wildcard;
  }

  function is_redundant(Pattern $pattern): bool {
    if ($this->is_covered()) {
      return true;
    } else if ($pattern instanceof WildcardPattern) {
      return false;
    }

    assert($pattern instanceof StrPattern);
    return in_array($pattern->value, $this->has_values);
  }

  function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
    } else if ($pattern instanceof StrPattern) {
      $this->has_values[] = $pattern->value;
    } else {
      assert(false, 'unreachable');
    }
  }

  function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    } else {
      return [ new WildcardPattern() ];
    }
  }
}
