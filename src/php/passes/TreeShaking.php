<?php

namespace Cthulhu\php\passes;

use Cthulhu\php;
use Cthulhu\php\visitor;

class TreeShaking {
  public static function apply(php\nodes\Program $prog): php\nodes\Program {
    $declared = [];
    $used = [];

    visitor\Visitor::walk($prog, [
      'ReferenceExpr' => function (visitor\Path $path) use (&$used) {
        $symbol_id = $path->node->reference->symbol->get_id();
        if (!in_array($symbol_id, $used)) {
          $used[] = $symbol_id;
        }
      },
      'FuncStmt' => function (visitor\Path $path) use (&$declared) {
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

    return visitor\Visitor::edit($prog, [
      'FuncStmt' => function (visitor\Path $path) use (&$to_remove) {
        $symbol_id = $path->node->head->name->symbol->get_id();
        if (in_array($symbol_id, $to_remove)) {
          $path->remove();
        }
      },
      'postorder(NamespaceNode)' => function (visitor\Path $path) {
        if ($path->node->block->is_empty()) {
          $path->remove();
        }
      },
    ]);
  }
}
