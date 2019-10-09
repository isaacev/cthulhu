<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class NativeItem extends Item {
  public $name;
  public $note;

  function __construct(Source\Span $span, IdentNode $name, FunctionAnnotation $note, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('NativeItem', $visitor_table)) {
      $visitor_table['NativeItem']($this);
    }

    $this->name->visit($visitor_table);
    $this->note->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'NativeItem',
      'name' => $this->name,
      'note' => $this->note,
    ];
  }
}
