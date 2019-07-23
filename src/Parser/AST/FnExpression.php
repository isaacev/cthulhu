<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class FnExpression extends Expression {
  public $from;
  public $parameters;
  public $return_annotation;
  public $body;

  function __construct(Point $from, array $parameters, Annotation $return_annotation, Block $body) {
    $this->from = $from;
    $this->parameters = $parameters;
    $this->return_annotation = $return_annotation;
    $this->body = $body;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->from;
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
