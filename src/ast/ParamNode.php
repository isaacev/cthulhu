<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ParamNode extends Node {
  public $name;
  public $note;

  function __construct(Source\Span $span, IdentNode $name, Annotation $note) {
    parent::__construct($span);
    $this->name = $name;
    $this->note = $note;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('ParamNode', $visitor_table)) {
      $visitor_table['ParamNode']($this);
    }

    $this->name->visit($visitor_table);
    $this->note->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'ParamNode',
      'name' => $this->name,
      'note' => $this->note
    ];
  }
}
