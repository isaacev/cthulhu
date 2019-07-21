<?php

namespace Cthulhu\Types;

use Cthulhu\Parser\AST;

class Checker {
  public static function check_expr(Scope $scope, AST\Expression $expr): Type {
    switch (true) {
      case $expr instanceof AST\NumLiteralExpression:
        return new NumType();
      case $expr instanceof AST\StrLiteralExpression:
        return new StrType();
      case $expr instanceof AST\Identifier:
        return Checker::check_identifier_expr($scope, $expr);
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check expression: ' . get_class($expr));
        // @codeCoverageIgnoreEnd
    }
  }

  public static function check_identifier_expr(Scope $scope, AST\Identifier $expr): Type {
    $name = $expr->name;
    if ($scope->has_local_variable($name)) {
      return $scope->get_local_variable($name);
    } else {
      throw new Errors\UndeclaredVariable($name);
    }
  }
}
