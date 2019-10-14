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

  public function visit(array $visitor_table): void {
    if (array_key_exists('FunctionAnnotation', $visitor_table)) {
      $visitor_table['FunctionAnnotation']($this);
    }

    foreach ($this->inputs as $input) {
      $input->visit($visitor_table);
    }
    $this->output->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FunctionAnnotation',
      'inputs' => $this->inputs,
      'output' => $this->output,
    ];
  }
}
