<?php

namespace Cthulhu\ir\patterns;

class Sum {
  public array $members;
  public bool $has_glob = false;
  public bool $has_wildcard = false;

  /**
   * @param Node[] $members
   */
  public function __construct(array $members) {
    $this->members = $members;
  }

  public function cardinality(): int {
    return count($this->members);
  }

  public function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
      return;
    }

    assert($pattern instanceof ListPattern);
    assert($pattern->cardinality() <= $this->cardinality());

    for ($i = 0; $i < $pattern->cardinality(); $i++) {
      $this->members[$i]->apply($pattern->elements[$i]);
    }

    if ($pattern->has_glob) {
      for ($i = $pattern->cardinality(); $i < $this->cardinality(); $i++) {
        $this->members[$i]->apply(new WildcardPattern());
      }

      $this->has_glob = true;
    }
  }
}
