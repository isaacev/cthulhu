<?php

namespace Cthulhu\ir\nodes;

class UnnamedVariantNode extends VariantNode {
  public $members;

  /**
   * UnnamedVariantNode constructor.
   * @param Name $name
   * @param Note[] $members
   */
  function __construct(Name $name, array $members) {
    parent::__construct($name);
    $this->members = $members;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->members
    );
  }
}
