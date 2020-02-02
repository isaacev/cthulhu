<?php

namespace Cthulhu\php\passes;

use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\nodes\AssignStmt;
use Cthulhu\php\nodes\Program;
use Cthulhu\php\nodes\ReturnStmt;
use Cthulhu\php\nodes\Variable;
use Cthulhu\php\nodes\VariableExpr;

class ReturnBackProp implements Pass {
  public static function apply(Program $prog): Program {
    $returned_vars = []; // [symbol id]

    Visitor::walk($prog, [
      'ReturnStmt' => function (ReturnStmt $ret) use (&$returned_vars) {
        if ($ret->expr instanceof VariableExpr) {
          $var_id          = $ret->expr->variable->symbol->get_id();
          $returned_vars[] = $var_id;
        }
      },
    ]);

    $new_prog = Visitor::edit($prog, [
      'AssignStmt' => function (AssignStmt $assign, EditablePath $path) use (&$returned_vars) {
        if ($assign->assignee instanceof Variable) {
          $var_id = $assign->assignee->symbol->get_id();
          if (in_array($var_id, $returned_vars)) {
            $path->replace_with(new ReturnStmt($assign->expr, null));
          }
        }
      },
      'exit(ReturnStmt)' => function (ReturnStmt $ret, EditablePath $path) use (&$returned_vars) {
        if ($ret->expr instanceof VariableExpr) {
          $var_id = $ret->expr->variable->symbol->get_id();
          if (in_array($var_id, $returned_vars)) {
            $path->remove();
          }
        }
      },
    ]);

    assert($new_prog instanceof Program);
    return $new_prog;
  }
}
