<?php

namespace Cthulhu\ast\nodes;

use Cthulhu\loc\Span;

class FunctionAnnotation extends Annotation {
  public array $inputs;
  public Annotation $output;

  /**
   * @param Span         $span
   * @param Annotation[] $inputs
   * @param Annotation   $output
   */
  public function __construct(Span $span, array $inputs, Annotation $output) {
    parent::__construct($span);
    $this->inputs = $inputs;
    $this->output = $output;
  }
}
