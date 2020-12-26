<?php

namespace Cthulhu\php\passes;

use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\nodes\AssignStmt;
use Cthulhu\php\nodes\Program;
use Cthulhu\php\nodes\SemiStmt;
use Cthulhu\php\nodes\Variable;
use Cthulhu\php\nodes\VariableExpr;

class VarReduction implements Pass {
  public static function apply(Program $prog): Program {
    $replacements = []; // symbol id -> expression
    $total_writes = []; // symbol id -> int

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

    $total_reads = []; // symbol id -> int

    $inc_read = function (int $var_id) use (&$total_reads) {
      if (array_key_exists($var_id, $total_reads)) {
        $total_reads[$var_id] += 1;
      } else {
        $total_reads[$var_id] = 1;
      }
    };

    $get_read = function (int $var_id) use (&$total_reads): int {
      if (array_key_exists($var_id, $total_reads)) {
        return $total_reads[$var_id];
      } else {
        return 0;
      }
    };

    Visitor::walk($new_prog, [
      'VariableExpr' => function (VariableExpr $usage) use (&$replacements, &$inc_read) {
        $var_id = $usage->variable->symbol->get_id();
        $inc_read($var_id);
      },
    ]);

    $new_prog = Visitor::edit($new_prog, [
      'exit(AssignStmt)' => function (AssignStmt $assignment, EditablePath $path) use (&$get_read) {
        if ($assignment->assignee instanceof Variable) {
          $var_id = $assignment->assignee->symbol->get_id();
          if ($get_read($var_id) <= 0) {
            $expr = $assignment->expr;
            $stmt = new SemiStmt($expr, null);
            $path->replace_with($stmt);
          }
        }
      },
    ]);

    assert($new_prog instanceof Program);
    return $new_prog;
  }
}
