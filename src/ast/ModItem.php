<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ModItem extends Item {
  public $name;
  public $items;

  function __construct(Source\Span $span, IdentNode $name, array $items, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->items = $items;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('ModItem', $visitor_table)) {
      $visitor_table['ModItem']($this);
    }

    $this->name->visit($visitor_table);
    foreach ($this->items as $item) {
      $item->visit($visitor_table);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'ModItem',
      'name' => $this->name,
      'items' => $this->items,
    ];
  }
}
