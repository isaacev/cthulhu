<?php

namespace Cthulhu\php\passes;

use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\nodes\AssignStmt;
use Cthulhu\php\nodes\Program;
use Cthulhu\php\nodes\Variable;
use Cthulhu\php\nodes\VariableExpr;

class VarReduction implements Pass {
  public static function apply(Program $prog): Program {
    $replacements = []; // symbol id -> expression
    $total_writes = [];

    Visitor::walk($prog, [
      'AssignStmt' => function (AssignStmt $assignment) use (&$total_writes) {
        if ($assignment->assignee instanceof Variable) {
          $var_id = $assignment->assignee->symbol->get_id();
          if (array_key_exists($var_id, $total_writes)) {
            $total_writes[$var_id]++;
          } else {
            $total_writes[$var_id] = 1;
          }
        }
      },
    ]);

    $new_prog = Visitor::edit($prog, [
      'exit(AssignStmt)' => function (AssignStmt $assignment, EditablePath $path) use (&$replacements, &$total_writes) {
        if ($assignment->assignee instanceof Variable) {
          if ($assignment->expr instanceof VariableExpr) {
            $var_id = $assignment->assignee->symbol->get_id();
            if ($total_writes[$var_id] === 1) {
              $expr                  = $assignment->expr;
              $replacements[$var_id] = $expr;
              $path->remove();
            }
          }
        }
      },
      'VariableExpr' => function (VariableExpr $usage, EditablePath $path) use (&$replacements) {
        $var_id = $usage->variable->symbol->get_id();
        if (array_key_exists($var_id, $replacements)) {
          $path->replace_with($replacements[$var_id]);
        }
      },
    ]);

    assert($new_prog instanceof Program);
    return $new_prog;
  }
}
