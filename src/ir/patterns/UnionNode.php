<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types\NamedVariant;
use Cthulhu\ir\types\OrderedVariant;
use Cthulhu\ir\types\UnionType;
use Cthulhu\ir\types\UnitVariant;

class UnionNode extends Node {
  protected UnionType $type;
  protected array $variants = [];
  protected bool $has_wildcard = false;
  protected bool $is_uncovered = true;

  public function __construct(UnionType $type) {
    $this->type = $type;
    foreach ($type->variants as $name => $form) {
      switch (true) {
        case $form instanceof UnitVariant:
          $this->variants[$name] = new UnitVariantNode($name);
          break;
        case $form instanceof OrderedVariant:
          $this->variants[$name] = new OrderedVariantNode($name, $form);
          break;
        case $form instanceof NamedVariant:
          $this->variants[$name] = new NamedVariantNode($name, $form);
          break;
        default:
          die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
      }
    }
  }

  public function is_covered(): bool {
    if ($this->has_wildcard) {
      return true;
    }

    foreach ($this->type->variants as $name => $form) {
      if ($this->variants[$name]->is_covered() === false) {
        return false;
      }
    }

    return true;
  }

  public function is_redundant(Pattern $pattern): bool {
    if ($pattern instanceof WildcardPattern) {
      return $this->is_covered();
    } else if ($pattern instanceof VariantPattern) {
      return $this->variants[$pattern->name]->is_redundant($pattern);
    } else {
      die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  public function apply(Pattern $pattern): void {
    $this->is_uncovered = false;

    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
      return;
    }

    assert($pattern instanceof VariantPattern);
    assert(array_key_exists($pattern->name, $this->variants));
    $this->variants[$pattern->name]->apply($pattern);
  }

  public function uncovered_patterns(): array {
    if ($this->is_uncovered) {
      return [ new WildcardPattern() ];
    } else if ($this->is_covered()) {
      return [];
    }

    $patterns = [];
    foreach ($this->variants as $variant) {
      $patterns = array_merge($patterns, $variant->uncovered_patterns());
    }
    return $patterns;
  }
}
