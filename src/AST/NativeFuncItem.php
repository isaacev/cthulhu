<?php

namespace Cthulhu\AST;

use Cthulhu\Source;

class NativeFuncItem extends Item {
  public $name;
  public $note;

  function __construct(Source\Span $span, IdentNode $name, FunctionAnnotation $note, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->note = $note;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('NativeFuncItem', $visitor_table)) {
      $visitor_table['NativeFuncItem']($this);
    }

    $this->name->visit($visitor_table);
    $this->note->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'NativeFuncItem',
      'name' => $this->name,
      'note' => $this->note,
    ];
  }
}
