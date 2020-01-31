<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types;

class OrderedFormNode extends FormNode {
  protected types\Tuple $type;
  protected array $child_nodes = [];

  public function __construct(string $name, types\Tuple $type) {
    parent::__construct($name);
    $this->type = $type;
  }

  public function is_covered(): bool {
    foreach ($this->type->members as $index => $type) {
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

  public function is_redundant(Pattern $pattern): bool {
    assert($pattern instanceof FormPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof OrderedFormFields);
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

  public function apply(Pattern $pattern): void {
    assert($pattern instanceof FormPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof OrderedFormFields);
    foreach ($pattern->fields->order as $index => $sub_pattern) {
      if (!array_key_exists($index, $this->child_nodes)) {
        $this->child_nodes[$index] = Node::from_type($this->type->members[$index]);
      }
      $this->child_nodes[$index]->apply($sub_pattern);
    }
  }

  public function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    }

    $order = [];
    foreach ($this->type->members as $index => $type) {
      $order[$index] = new WildcardPattern();
    }
    $fields = new OrderedFormFields($order);
    return [ new FormPattern($this->name, $fields) ];
  }
}
