<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class FuncExpr extends Expr {
  public $params;
  public $return_annotation;
  public $block;

  function __construct(Span $span, array $params, Annotation $return_annotation, BlockNode $block) {
    parent::__construct($span);
    $this->params = $params;
    $this->return_annotation = $return_annotation;
    $this->block = $block;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('FuncExpr', $visitor_table)) {
      $visitor_table['FuncExpr']($this);
    }

    $this->block->visit($visitor_table);
  }

  public function jsonSerialize() {
    $params_json = array_map(function ($param) {
      return [
        'name' => $param['name'],
        'annotation' => $param['annotation']->jsonSerialize()
      ];
    }, $this->params);

    return [
      'type' => 'FuncExpr',
      'params' => $params_json,
      'return_annotation' => $this->return_annotation->jsonSerialize(),
      'block' => $block->jsonSerialize(),
    ];
  }
}
