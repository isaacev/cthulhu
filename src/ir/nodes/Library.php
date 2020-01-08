<?php

namespace Cthulhu\ir\nodes;

class Library extends Node {
  public Name $name;
  public array $items;

  /**
   * @param Name   $name
   * @param Item[] $items
   */
  public function __construct(Name $name, array $items) {
    parent::__construct();
    $this->name  = $name;
    $this->items = $items;
  }

  public function get_name(): string {
    return $this->name->value;
  }

  public function children(): array {
    return array_merge(
      [ $this->name ],
      $this->items
    );
  }

  public function __toString(): string {
    return $this->get_name();
  }
}
