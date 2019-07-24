<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Span;

class FnExpression extends Expression {
  public $parameters;
  public $return_annotation;
  public $body;

  function __construct(Span $span, array $parameters, Annotation $return_annotation, Block $body) {
    parent::__construct($span);
    $this->parameters = $parameters;
    $this->return_annotation = $return_annotation;
    $this->body = $body;
  }

  public function jsonSerialize() {
    $params = array_map(function ($param) {
      return [
        'name' => $param['name'],
        'annotation' => $param['annotation']->jsonSerialize()
      ];
    }, $this->parameters);

    return [
      'type' => 'FnExpression',
      'parameters' => $params,
      'return_annotation' => $this->return_annotation->jsonSerialize(),
      'body' => $this->body->jsonSerialize(),
    ];
  }
}
