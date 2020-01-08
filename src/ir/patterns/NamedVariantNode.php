<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types;

class NamedVariantNode extends VariantNode {
  protected types\NamedVariant $types;
  protected array $child_nodes = [];

  public function __construct(string $name, types\NamedVariant $types) {
    parent::__construct($name);
    $this->types = $types;
  }

  public function is_covered(): bool {
    foreach ($this->types->mapping as $name => $type) {
      if (array_key_exists($name, $this->child_nodes)) {
        if ($this->child_nodes[$name]->is_covered() === false) {
          return false;
        }
      } else {
        return false;
      }
    }
    return true;
  }

  public function is_redundant(Pattern $pattern): bool {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof NamedVariantFields);
    foreach ($pattern->fields->mapping as $name => $sub_pattern) {
      if (array_key_exists($name, $this->child_nodes)) {
        if ($this->child_nodes[$name]->is_redundant($sub_pattern) === false) {
          return false;
        }
      } else {
        return false;
      }
    }
    return true;
  }

  public function apply(Pattern $pattern): void {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof NamedVariantFields);
    foreach ($pattern->fields->mapping as $name => $sub_pattern) {
      if (!array_key_exists($name, $this->child_nodes)) {
        $this->child_nodes[$name] = Node::from_type($this->types->mapping[$name]);
      }
      $this->child_nodes[$name]->apply($sub_pattern);
    }
  }

  public function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    }

    $mapping = [];
    foreach ($this->types->mapping as $name => $type) {
      $mapping[$name] = new WildcardPattern();
    }
    $fields = new NamedVariantFields($mapping);
    return [ new VariantPattern($this->name, $fields) ];
  }
}
