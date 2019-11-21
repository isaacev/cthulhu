<?php

namespace Cthulhu\ir\patterns;

class UnitVariantNode extends VariantNode {
  protected $has_match = false;

  function is_covered(): bool {
    return $this->has_match;
  }

  function is_redundant(Pattern $pattern): bool {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields === null);
    return $this->is_covered();
  }

  function apply($pattern): void {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields === null);
    $this->has_match = true;
  }

  function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    } else {
      return [ new VariantPattern($this->name, null) ];
    }
  }
}
