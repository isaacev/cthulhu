<?php

namespace Cthulhu\ir\patterns;

class UnitVariantNode extends VariantNode {
  protected bool $has_match = false;

  public function is_covered(): bool {
    return $this->has_match;
  }

  public function is_redundant(Pattern $pattern): bool {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields === null);
    return $this->is_covered();
  }

  public function apply($pattern): void {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields === null);
    $this->has_match = true;
  }

  public function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    } else {
      return [ new VariantPattern($this->name, null) ];
    }
  }
}
