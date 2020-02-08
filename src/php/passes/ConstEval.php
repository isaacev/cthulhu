<?php

namespace Cthulhu\php\passes;

use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\nodes;
use Cthulhu\val\StringValue;

class ConstEval implements Pass {
  public static function apply(nodes\Program $prog): nodes\Program {
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

  private static function string_check(nodes\BinaryExpr $binary): ?nodes\StrLiteral {
    if ($binary->left instanceof nodes\StrLiteral && $binary->right instanceof nodes\StrLiteral) {
      if ($binary->operator === '.') {
        return new nodes\StrLiteral($binary->left->value->append($binary->right->value));
      }
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
