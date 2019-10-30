<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class FnItem extends Item {
  public $name;
  public $params;
  public $returns;
  public $body;

  function __construct(Source\Span $span, IdentNode $name, array $params, Annotation $returns, BlockNode $body, array $attrs) {
    parent::__construct($span, $attrs);
    $this->name = $name;
    $this->params = $params;
    $this->returns = $returns;
    $this->body = $body;
  }
}
