<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FunctionAnnotation extends Annotation {
  public $inputs;
  public $output;

  function __construct(Source\Span $span, array $inputs, Annotation $output) {
    parent::__construct($span);
    $this->inputs = $inputs;
    $this->output = $output;
  }
}
