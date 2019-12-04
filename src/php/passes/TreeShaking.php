<?php

namespace Cthulhu\php\passes;

use Cthulhu\php;
use Cthulhu\php\visitor;

class TreeShaking {
  public static function apply(php\nodes\Program $prog): php\nodes\Program {
    $declared = [];
    $used     = [];

    visitor\Visitor::walk($prog, [
      'ReferenceExpr' => function (visitor\Path $path) use (&$used) {
        assert($path->node instanceof php\nodes\ReferenceExpr);
        $symbol_id = $path->node->reference->symbol->get_id();
        if (!in_array($symbol_id, $used)) {
          $used[] = $symbol_id;
        }
      },
      'FuncStmt' => function (visitor\Path $path) use (&$declared) {
        assert($path->node instanceof php\nodes\FuncStmt);
        $symbol_id = $path->node->head->name->symbol->get_id();
        if (!in_array($symbol_id, $declared)) {
          $declared[] = $symbol_id;
        }
      },
    ]);

    // Remove any symbols ids that are declared but not used
    $to_remove = [];
    foreach ($declared as $id) {
      if (!in_array($id, $used)) {
        $to_remove[] = $id;
      }
    }

    $new_prog = visitor\Visitor::edit($prog, [
      'FuncStmt' => function (visitor\Path $path) use (&$to_remove) {
        assert($path->node instanceof php\nodes\FuncStmt);
        $symbol_id = $path->node->head->name->symbol->get_id();
        if (in_array($symbol_id, $to_remove)) {
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
