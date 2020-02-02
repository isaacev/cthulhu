<?php

namespace Cthulhu\ir\nodes;

class NamedForm extends Form {
  public array $mapping;

  /**
   * @param Name   $name
   * @param Name[] $mapping
   */
  public function __construct(Name $name, array $mapping) {
    parent::__construct($name);
    $this->mapping = $mapping;
  }

  public function children(): array {
    return array_merge([ $this->name ], array_values($this->mapping));
  }

  public function from_children(array $children): NamedForm {
    $mapping = array_combine(array_keys($this->mapping), array_slice($children, 1));
    return new NamedForm($children[0], $mapping);
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('form')
      ->space()
      ->then($this->name)
      ->paren_left()
      ->each(array_values($this->mapping), (new Builder)
        ->space())
      ->paren_right()
      ->paren_right();
  }
}
