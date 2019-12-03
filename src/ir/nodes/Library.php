<?php

namespace Cthulhu\ir\nodes;

class Library extends Node {
  public Name $name;
  public array $items;

  /**
   * @param Name   $name
   * @param Item[] $items
   */
  function __construct(Name $name, array $items) {
    parent::__construct();
    $this->name  = $name;
    $this->items = $items;
  }

  function get_name(): string {
    return $this->name->value;
  }

  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->items
    );
  }

  function __toString(): string {
    return $this->get_name();
  }
}
