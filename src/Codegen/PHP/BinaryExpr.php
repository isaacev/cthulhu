<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

class BinaryExpr extends Expr {
  public $operator;
  public $left;
  public $right;

  function __construct(string $operator, Expr $left, Expr $right) {
    $this->operator = $operator;
    $this->left = $left;
    $this->right = $right;
  }

  public function write(Writer $writer): Writer {
    return $writer->node($this->left)
                  ->operator($this->operator)
                  ->node($this->right);
  }

  public function jsonSerialize() {
    return [
      'type' => 'BinaryExpr',
      'operator' => $this->operator,
      'left' => $this->left->jsonSerialize(),
      'right' => $this->right->jsonSerialize()
    ];
  }
}
