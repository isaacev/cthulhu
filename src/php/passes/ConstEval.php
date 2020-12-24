<?php

namespace Cthulhu\php\passes;

use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\nodes;
use Cthulhu\val\StringValue;

class ConstEval implements Pass {
  public static function apply(nodes\Program $prog, array $skip): nodes\Program {
    if (in_array('const-eval', $skip)) {
      return $prog;
    }

    $new_prog = Visitor::edit($prog, [
      'exit(BinaryExpr)' => function (nodes\BinaryExpr $binary, EditablePath $path) {
        $replacement = (
          self::string_check($binary) ??
          self::int_check($binary) ??
          null
        );

        if ($replacement) {
          $path->replace_with($replacement);
        }
      },
      'exit(CastExpr)' => function (nodes\CastExpr $cast, EditablePath $path) {
        if ($cast->to_type === 'string') {
          if ($cast->expr instanceof nodes\FloatLiteral) {
            $raw     = (string)$cast->expr->value->value;
            $value   = $raw;
            $escaped = '"' . $value . '"';
            $path->replace_with(
              new nodes\StrLiteral(
                new StringValue($raw, $value, $escaped)));
          } else if ($cast->expr instanceof nodes\IntLiteral) {
            $raw     = (string)$cast->expr->value->value;
            $value   = $raw;
            $escaped = '"' . $value . '"';
            $path->replace_with(
              new nodes\StrLiteral(
                new StringValue($raw, $value, $escaped)));
          }
        }
      },
    ]);

    assert($new_prog instanceof nodes\Program);
    return $new_prog;
  }

  private static function string_check(nodes\BinaryExpr $binary): ?nodes\Expr {
    if ($binary->operator !== '.') {
      return null;
    }

    $left  = $binary->left;
    $right = $binary->right;
    if ($left instanceof nodes\StrLiteral) {
      if ($right instanceof nodes\StrLiteral) {
        // matches concat(STR_1, STR_2) -> STR_12
        return new nodes\StrLiteral($left->value->append($right->value));
      } else if ($right instanceof nodes\BinaryExpr
        && $right->operator === '.'
        && $right->left instanceof nodes\StrLiteral) {
        // matches concat(STR_1, concat(STR_2, EXPR)) -> concat(STR_12, EXPR)
        $new_left = new nodes\StrLiteral($left->value->append($right->left->value));
        return new nodes\BinaryExpr('.', $new_left, $right->right);
      }
    } else if ($right instanceof nodes\StrLiteral
      && $left instanceof nodes\BinaryExpr
      && $left->operator === '.'
      && $left->right instanceof nodes\StrLiteral) {
      // matches concat(concat(EXPR, STR_1), STR_2) -> concat(EXPR, STR_12)
      $new_right = new nodes\StrLiteral($left->right->value->append($right->value));
      return new nodes\BinaryExpr('.', $left->left, $new_right);
    }
    return null;
  }

  private static function int_check(nodes\BinaryExpr $binary): ?nodes\IntLiteral {
    if ($binary->left instanceof nodes\IntLiteral && $binary->right instanceof nodes\IntLiteral) {
      switch ($binary->operator) {
        case '+':
          $value = $binary->left->value->add($binary->right->value);
          return new nodes\IntLiteral($value);
        case '-':
          $value = $binary->left->value->subtract($binary->right->value);
          return new nodes\IntLiteral($value);
        case '*':
          $value = $binary->left->value->multiply($binary->right->value);
          return new nodes\IntLiteral($value);
      }
    }
    return null;
  }
}
