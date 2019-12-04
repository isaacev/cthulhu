<?php

namespace Cthulhu\php\passes;

use Cthulhu\php;
use Cthulhu\php\visitor;

class TreeShaking {
  public static function apply(php\nodes\Program $prog): php\nodes\Program {
    $stack = [];
    $graph = [];
    $reach = [];

    $add_edge = function (int $from, int $to) use (&$graph) {
      if (array_key_exists($from, $graph) === false) {
        $graph[$from] = [ $to ];
      } else if (in_array($to, $graph[$from]) === false) {
        $graph[$from][] = $to;
      }
    };

    visitor\Visitor::walk($prog, [
      'preorder(FuncStmt)' => function (visitor\Path $path) use (&$stack) {
        assert($path->node instanceof php\nodes\FuncStmt);
        array_push($stack, $path->node->head->name->symbol->get_id());
      },
      'postorder(FuncStmt)' => function () use (&$stack) {
        array_pop($stack);
      },
      'ClassStmt' => function (visitor\Path $path) use (&$add_edge) {
        assert($path->node instanceof php\nodes\ClassStmt);
        $class_id = $path->node->name->symbol->get_id();
        if ($path->node->parent_class !== null) {
          $parent_id = $path->node->parent_class->symbol->get_id();
          $add_edge($class_id, $parent_id);
        }
      },
      'ReferenceExpr' => function (visitor\Path $path) use (&$stack, &$add_edge, &$reach) {
        assert($path->node instanceof php\nodes\ReferenceExpr);
        $ref_id       = $path->node->reference->symbol->get_id();
        $func_stmt_id = end($stack);
        if ($func_stmt_id === false) {
          // If the ReferenceExpr is not inside of a function that it will
          // always be run by the program so mark it as already reachable.
          $reach[$ref_id] = false;
        } else {
          $add_edge($func_stmt_id, $ref_id);
        }
      },
    ]);

    $queue = array_keys($reach);
    while ($next_id = array_shift($queue)) {
      if (isset($reach[$next_id]) && $reach[$next_id] === true) {
        continue;
      }

      $reach[$next_id] = true;
      if (array_key_exists($next_id, $graph)) {
        array_push($queue, ...$graph[$next_id]);
      }
    }

    $new_prog = visitor\Visitor::edit($prog, [
      'FuncStmt' => function (visitor\Path $path) use (&$reach) {
        assert($path->node instanceof php\nodes\FuncStmt);
        $symbol_id = $path->node->head->name->symbol->get_id();
        if (array_key_exists($symbol_id, $reach) === false) {
          $path->remove();
        }
      },
      'ClassStmt' => function (visitor\Path $path) use (&$reach) {
        assert($path->node instanceof php\nodes\ClassStmt);
        $symbol_id = $path->node->name->symbol->get_id();
        if (array_key_exists($symbol_id, $reach) === false) {
          $path->remove();
        }
      },
      'postorder(NamespaceNode)' => function (visitor\Path $path) {
        assert($path->node instanceof php\nodes\NamespaceNode);
        if ($path->node->block->is_empty()) {
          $path->remove();
        }
      },
    ]);

    assert($new_prog instanceof php\nodes\Program);
    return $new_prog;
  }
}
