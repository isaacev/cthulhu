<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types;

class OrderedVariantNode extends VariantNode {
  protected types\OrderedVariant $types;
  protected array $child_nodes = [];

  function __construct(string $name, types\OrderedVariant $types) {
    parent::__construct($name);
    $this->types = $types;
  }

  function is_covered(): bool {
    foreach ($this->types->order as $index => $type) {
      if (array_key_exists($index, $this->child_nodes)) {
        if ($this->child_nodes[$index]->is_covered() === false) {
          return false;
        }
      } else {
        return false;
      }
    }
    return true;
  }

  function is_redundant(Pattern $pattern): bool {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof OrderedVariantFields);
    foreach ($pattern->fields->order as $index => $sub_pattern) {
      if (array_key_exists($index, $this->child_nodes)) {
        if ($this->child_nodes[$index]->is_redundant($sub_pattern) === false) {
          return false;
        }
      } else {
        return false;
      }
    }
    return true;
  }

  function apply(Pattern $pattern): void {
    assert($pattern instanceof VariantPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof OrderedVariantFields);
    foreach ($pattern->fields->order as $index => $sub_pattern) {
      if (!array_key_exists($index, $this->child_nodes)) {
        $this->child_nodes[$index] = Node::from_type($this->types->order[$index]);
      }
      $this->child_nodes[$index]->apply($sub_pattern);
    }
  }

  function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    }

    $order = [];
    foreach ($this->types->order as $index => $type) {
      $order[$index] = new WildcardPattern();
    }
    $fields = new OrderedVariantFields($order);
    return [ new VariantPattern($this->name, $fields) ];
  }
}
