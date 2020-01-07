<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class FieldDeclNode extends Node {
  public LowerNameNode $name;
  public Annotation $note;

  public function __construct(Span $span, LowerNameNode $name, Annotation $note) {
    parent::__construct($span);
    $this->name = $name;
    $this->note = $note;
  }
}
