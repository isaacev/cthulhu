<?php

namespace Cthulhu\AST;

use Cthulhu\Parser\Lexer\Span;

class FnStmt extends Stmt {
  public $name;
  public $params;
  public $return;
  public $body;

  function __construct(Span $span, IdentNode $name, array $params, ?Annotation $return, BlockNode $body) {
    parent::__construct($span);
    $this->name = $name;
    $this->params = $params;
    $this->return = $return;
    $this->body = $body;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('FnStmt', $visitor_table)) {
      $visitor_table['FnStmt']($this);
    }

    $this->name->visit($visitor_table);

    foreach ($this->params as $param) {
      $param->visit($visitor_table);
    }

    $this->return->visit($visitor_table);
    $this->body->visit($visitor_table);
  }

  public function jsonSerialize() {
    return [
      'type' => 'FnStmt',
      'name' => $this->name,
      'params' => $this->params,
      'return' => $this->return,
      'body' => $this->body
    ];
  }
}
