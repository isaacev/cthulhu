<?php

namespace Cthulhu\php\passes;

use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\php\names\Scope;
use Cthulhu\php\names\Symbol;
use Cthulhu\php\nodes;
use Cthulhu\val\BooleanValue;

class TailCall implements Pass {
  public static function apply(nodes\Program $prog): nodes\Program {
    /* @var Symbol|null $current_func_symbol */
    $current_func_symbol = null;

    /* @var int[] $tail_recursive_defs */
    $tail_recursive_defs = [];

    Visitor::walk($prog, [
      'enter(FuncStmt)' => function (nodes\FuncStmt $stmt) use (&$current_func_symbol) {
        $current_func_symbol = $stmt->head->name->symbol;
      },
      'CallExpr' => function (nodes\CallExpr $ret, Path $path) use (&$current_func_symbol, &$tail_recursive_defs) {
        if ($ret->callee instanceof nodes\ReferenceExpr && $current_func_symbol) {
          $ref_id = $ret->callee->reference->symbol->get_id();
          if ($ref_id === $current_func_symbol->get_id() && self::path_is_tail($path->parent)) {
            $tail_recursive_defs[$ref_id] = true;
            $ret->set('tail-call', true);
          }
        }
      },
      'exit(FuncStmt)' => function () use (&$current_func_symbol) {
        $current_func_symbol = null;
      },
    ]);

    /* @var bool[] $is_tail_stmt */
    $is_tail_stmt = [ false ];

    /* @var nodes\Variable[][] $recursive_vars */
    $recursive_vars = [];

    $new_prog = Visitor::edit($prog, [
      'enter(FuncStmt)' => function (nodes\FuncStmt $stmt) use (&$tail_recursive_defs, &$is_tail_stmt, &$recursive_vars) {
        $func_id = $stmt->head->name->symbol->get_id();
        if (array_key_exists($func_id, $tail_recursive_defs)) {
          $is_tail_stmt[] = true;
          $recursive_vars = $stmt->head->params;

          /* @var Scope $scope */
          $scope = $stmt->get('scope');

          $recursive_vars = [];
          foreach ($stmt->head->params as $param_var) {
            $alt_text         = $scope->use_name($scope->next_tmp_name());
            $alt_var          = new nodes\Variable($alt_text, new Symbol());
            $recursive_vars[] = [ $param_var, $alt_var ];
          }
        }
      },
      'exit(FuncStmt)' => function (nodes\FuncStmt $stmt, EditablePath $path) use (&$tail_recursive_defs, &$is_tail_stmt, &$recursive_vars) {
        $func_id = $stmt->head->name->symbol->get_id();
        if (array_key_exists($func_id, $tail_recursive_defs)) {
          array_pop($is_tail_stmt);

          $consequent = $stmt->body->stmt;
          $alt_params = [];
          foreach (array_reverse($recursive_vars) as $pair) {
            [ $param_var, $alt_var ] = $pair;
            array_unshift($alt_params, $alt_var);
            $consequent = new nodes\AssignStmt(
              $param_var,
              new nodes\VariableExpr($alt_var),
              $consequent);
          }

          $condition  = new nodes\BoolLiteral(BooleanValue::from_scalar(true));
          $while_stmt = new nodes\WhileStmt($condition, new nodes\BlockNode($consequent), null);

          $body      = new nodes\BlockNode($while_stmt);
          $func_head = new nodes\FuncHead($stmt->head->name, $alt_params);
          $func_stmt = new nodes\FuncStmt($func_head, $body, [], null);
          $path->replace_with($func_stmt);

          // By removing the function's symbol ID from the list of tail
          // recursive definitions, an infinite loop is prevented since after
          // a call to EditablePath#replace_with, callbacks are re-applied to
          // the replacement node.
          unset($tail_recursive_defs[$func_id]);
        }
      },

      'enter(IfStmt|WhileStmt)' => function (nodes\Stmt $stmt) use (&$is_tail_stmt) {
        if (end($is_tail_stmt)) {
          array_push($is_tail_stmt, $stmt->next ? false : true);
        }
      },
      'exit(IfStmt|WhileStmt)' => function () use (&$is_tail_stmt) {
        if (end($is_tail_stmt)) {
          array_pop($is_tail_stmt);
        }
      },

      'exit(SemiStmt)' => function (nodes\SemiStmt $stmt, EditablePath $path) use (&$is_tail_stmt, &$recursive_vars) {
        if (end($is_tail_stmt) && $stmt->next === null) {
          if ($stmt->expr instanceof nodes\CallExpr && $stmt->expr->get('tail-call')) {
            assert(count($stmt->expr->args) === count($recursive_vars));

            $successor = new nodes\ContinueStmt(null);
            $args      = $stmt->expr->args;
            foreach (array_reverse($recursive_vars) as $index => $pair) {
              $arg       = $args[count($args) - $index - 1];
              $successor = new nodes\AssignStmt($pair[1], $arg, $successor);
            }
            $path->replace_with($successor);
          } else {
            $successor = new nodes\ReturnStmt(null, null);
            $path->replace_with($stmt->from_successor($successor));
          }
        }
      },

      'exit(ReturnStmt)' => function (nodes\ReturnStmt $stmt, EditablePath $path) use (&$is_tail_stmt, &$recursive_vars) {
        if (end($is_tail_stmt)) {
          if ($stmt->expr instanceof nodes\CallExpr && $stmt->expr->get('tail-call')) {
            assert(count($stmt->expr->args) === count($recursive_vars));

            $successor = new nodes\ContinueStmt(null);
            $args      = $stmt->expr->args;
            foreach (array_reverse($recursive_vars) as $index => $pair) {
              $arg       = $args[count($args) - $index - 1];
              $successor = new nodes\AssignStmt($pair[1], $arg, $successor);
            }
            $path->replace_with($successor);
          }
        }
      },
    ]);

    assert($new_prog instanceof nodes\Program);
    return $new_prog;
  }

  private static function path_is_tail(?Path $path): bool {
    if ($path === null) {
      return false;
    }

    if ($path->node instanceof nodes\FuncStmt || $path->node instanceof nodes\ReturnStmt) {
      return true;
    } else if ($path->node instanceof nodes\SemiStmt || $path->node instanceof nodes\IfStmt) {
      if ($path->node->next) {
        return false;
      } else {
        return self::path_is_tail($path->parent);
      }
    } else if ($path->node instanceof nodes\BlockNode) {
      return self::path_is_tail($path->parent);
    } else {
      return false;
    }
  }
}
