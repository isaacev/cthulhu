<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class FieldDeclNode extends Node {
  public LowerName $name;
  public Note $note;

  public function __construct(Span $span, LowerName $name, Note $note) {
    parent::__construct($span);
    $this->name = $name;
    $this->note = $note;
  }
}
