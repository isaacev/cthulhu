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
      default:
        // @codeCoverageIgnoreStart
        throw new \Exception('cannot check expression: ' . get_class($expr));
        // @codeCoverageIgnoreEnd
    }
  }
}
