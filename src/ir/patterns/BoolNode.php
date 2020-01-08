<?php

namespace Cthulhu\ir\patterns;

class BoolNode extends Node {
  protected bool $has_wildcard = false;
  protected bool $has_true = false;
  protected bool $has_false = false;

  function is_covered(): bool {
    return $this->has_wildcard || ($this->has_true && $this->has_false);
  }

  function is_redundant(Pattern $pattern): bool {
    if ($this->is_covered()) {
      return true;
    } else if ($pattern instanceof WildcardPattern) {
      return false;
    }

    assert($pattern instanceof BoolPattern);
    if ($pattern->value->value) {
      return $this->has_true;
    } else {
      return $this->has_false;
    }
  }

  function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
    } else if ($pattern instanceof BoolPattern) {
      if ($pattern->value->value) {
        $this->has_true = true;
      } else {
        $this->has_false = true;
      }
    } else {
      die('unreachable');
    }
  }

  function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    } else if ($this->has_true) {
      return [ new BoolPattern(false) ];
    } else if ($this->has_false) {
      return [ new BoolPattern(true) ];
    } else {
      return [ new WildcardPattern() ];
    }
  }
}
