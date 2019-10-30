<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class ParamNode extends Node {
  public $name;
  public $note;

  function __construct(Source\Span $span, LowerNameNode $name, Annotation $note) {
    parent::__construct($span);
    $this->name = $name;
    $this->note = $note;
  }
}
