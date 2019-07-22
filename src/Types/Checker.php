<?php

namespace Cthulhu\Types;

use Cthulhu\Parser\AST;

class Checker {
  public static function check_stmt(AST\Statement $stmt, ?Binding $binding): Binding {
    switch (true) {
      case $stmt instanceof AST\LetStatement:
        return Checker::check_let_stmt($stmt, $binding);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check statement: ' . get_class($stmt));
        // @codeCoverageIgnoreEnd
    }
  }

  private static function check_let_stmt(AST\LetStatement $stmt, ?Binding $binding): Binding {
    return new Binding($binding, $stmt->name, Checker::check_expr($stmt->expression, $binding));
  }

  public static function check_expr(AST\Expression $expr, ?Binding $binding): Type {
    switch (true) {
      case $expr instanceof AST\NumLiteralExpression:
        return new NumType();
      case $expr instanceof AST\StrLiteralExpression:
        return new StrType();
      case $expr instanceof AST\Identifier:
        return Checker::check_identifier_expr($expr, $binding);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check expression: ' . get_class($expr));
        // @codeCoverageIgnoreEnd
    }
  }

  public static function check_identifier_expr(AST\Identifier $expr, ?Binding $binding): Type {
    $resolved = $binding ? $binding->resolve($expr->name) : null;
    if ($resolved === null) {
      throw new Errors\UndeclaredVariable($expr->name);
    } else {
      return $resolved;
    }
  }
}
