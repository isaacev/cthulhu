<?php

namespace Cthulhu\ir\patterns;

class IntNode extends Node {
  protected bool $has_wildcard = false;
  protected array $has_values = [];

  public function is_covered(): bool {
    return $this->has_wildcard;
  }

  public function is_redundant(Pattern $pattern): bool {
    if ($this->is_covered()) {
      return true;
    } else if ($pattern instanceof WildcardPattern) {
      return false;
    }

    assert($pattern instanceof IntPattern);
    return in_array($pattern->value, $this->has_values);
  }

  public function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
    } else if ($pattern instanceof IntPattern) {
      $this->has_values[] = $pattern->value;
    } else {
      die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
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
