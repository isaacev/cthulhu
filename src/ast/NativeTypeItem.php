<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class NativeTypeItem extends Item {
  public $name;

  function __construct(Source\Span $span, IdentNode $name, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('NativeTypeItem', $visitor_table)) {
      $visitor_table['NativeTypeItem']($this);
    }

    $this->name->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'NativeTypeItem',
      'name' => $this->name,
    ];
  }
}
