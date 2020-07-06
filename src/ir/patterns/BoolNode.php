<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\lib\panic\Panic;
use Cthulhu\val\BooleanValue;

class BoolNode extends Node {
  protected bool $has_wildcard = false;
  protected bool $has_true = false;
  protected bool $has_false = false;

  public function is_covered(): bool {
    return $this->has_wildcard || ($this->has_true && $this->has_false);
  }

  public function is_redundant(Pattern $pattern): bool {
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

  public function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
    } else if ($pattern instanceof BoolPattern) {
      if ($pattern->value->value) {
        $this->has_true = true;
      } else {
        $this->has_false = true;
      }
    } else {
      die(Panic::if_reached(__LINE__, __FILE__));
    }
  }

  public function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    } else if ($this->has_true) {
      return [ new BoolPattern(BooleanValue::from_scalar(false)) ];
    } else if ($this->has_false) {
      return [ new BoolPattern(BooleanValue::from_scalar(true)) ];
    } else {
      return [ new WildcardPattern() ];
    }
  }
}
