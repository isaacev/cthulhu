<?php

namespace Cthulhu\php\passes;

use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\nodes\BinaryExpr;
use Cthulhu\php\nodes\Literal;
use Cthulhu\php\nodes\Program;
use Cthulhu\php\nodes\SemiStmt;
use Cthulhu\php\nodes\VariableExpr;

class UnusedExprs implements Pass {
  public static function apply(Program $prog, array $skip): Program {
    if (in_array('unused-exprs', $skip)) {
      return $prog;
    }

    $new_prog = Visitor::edit($prog, [
      'Literal' => function (Literal $literal) {
        $literal->set('const', true);
      },
      'VariableExpr' => function (VariableExpr $expr) {
        $expr->set('const', true);
      },
      'exit(BinaryExpr)' => function (BinaryExpr $expr) {
        if ($expr->left->get('const') && $expr->right->get('const')) {
          $expr->set('const', true);
        }
      },
      'exit(SemiStmt)' => function (SemiStmt $stmt, EditablePath $path) {
        if ($stmt->expr->get('const')) {
          $path->remove();
        }
      },
    ]);

    assert($new_prog instanceof Program);
    return $new_prog;
  }
}
