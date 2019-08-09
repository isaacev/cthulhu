<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class FuncExpr extends Expr {
  public $params;
  public $return_annotation;
  public $body;

  function __construct(Span $span, array $params, Annotation $return_annotation, array $body) {
    parent::__construct($span);
    $this->params = $params;
    $this->return_annotation = $return_annotation;
    $this->body = $body;
  }

  public function jsonSerialize() {
    $params_json = array_map(function ($param) {
      return [
        'name' => $param['name'],
        'annotation' => $param['annotation']->jsonSerialize()
      ];
    }, $this->params);

    $body_json = array_map(function ($stmt) {
      return $stmt->jsonSerialize();
    }, $this->body);

    return [
      'type' => 'FuncExpr',
      'params' => $params_json,
      'return_annotation' => $this->return_annotation->jsonSerialize(),
      'body' => $body_json,
    ];
  }
}
