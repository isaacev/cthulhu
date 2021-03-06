<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\panic\Panic;
use Cthulhu\php\Builder;

class BinaryExpr extends Expr {
  public string $operator;
  public Expr $left;
  public Expr $right;

  public function __construct(string $operator, Expr $left, Expr $right) {
    parent::__construct();
    $this->operator = $operator;
    $this->left     = $left;
    $this->right    = $right;
  }

  public function children(): array {
    return [ $this->left, $this->right ];
  }

  public function from_children(array $nodes): Node {
    return new self($this->operator, $nodes[0], $nodes[1]);
  }

  public function precedence(): int {
    switch ($this->operator) {
      case '?:':
        return Precedence::TERNARY;
      case '&&':
        return Precedence::BOOLEAN_SYMBOL_AND;
      case '||':
        return Precedence::BOOLEAN_SYMBOL_OR;
      case '==':
      case '!=':
      case '===':
      case '!==':
        return Precedence::EQUALITY_COMPARISON;
      case '<':
      case '>':
      case '<=':
      case '>=':
        return Precedence::ORDERED_COMPARISON;
      case '+':
      case '-':
        return Precedence::SUM;
      case '.':
        return Precedence::STRING_CONCAT;
      case '*':
      case '/':
        return Precedence::PRODUCT;
      case 'instanceof':
        return Precedence::INSTANCE_OF;
      case '**':
        return Precedence::EXPONENT;
      default:
        Panic::with_reason(__LINE__, __FILE__, "unknown precedence for `$this->operator` operator");
    }
  }

  public function build(): Builder {
    return (new Builder)
      ->expr($this->left, $this->precedence())
      ->space()
      ->operator($this->operator)
      ->space()
      ->expr($this->right, $this->precedence());
  }
}
