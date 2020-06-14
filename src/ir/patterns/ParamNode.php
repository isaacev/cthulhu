<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\lib\panic\Panic;

class ParamNode extends Node {
  protected bool $has_wildcard = false;

  public function is_covered(): bool {
    return $this->has_wildcard;
  }

  public function is_redundant(Pattern $pattern): bool {
    return $this->is_covered();
  }

  public function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
    } else {
      Panic::if_reached(__LINE__, __FILE__);
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
