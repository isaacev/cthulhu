<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types;

class NamedFormNode extends FormNode {
  protected types\Record $type;
  protected array $child_nodes = [];

  public function __construct(string $name, types\Record $type) {
    parent::__construct($name);
    $this->type = $type;
  }

  public function is_covered(): bool {
    foreach ($this->type->fields as $name => $type) {
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
    assert($pattern instanceof FormPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof NamedFormFields);
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
    assert($pattern instanceof FormPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof NamedFormFields);
    foreach ($pattern->fields->mapping as $name => $sub_pattern) {
      if (!array_key_exists($name, $this->child_nodes)) {
        $this->child_nodes[$name] = Node::from_type($this->type->fields[$name]);
      }
      $this->child_nodes[$name]->apply($sub_pattern);
    }
  }

  public function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    }

    $mapping = [];
    foreach ($this->type->fields as $name => $type) {
      $mapping[$name] = new WildcardPattern();
    }
    $fields = new NamedFormFields($mapping);
    return [ new FormPattern($this->name, $fields) ];
  }
}
