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
        var_dump($path->node);
        $id = $path->node->reference->symbol->id;
        if (!in_array($id, $used)) {
          $used[] = $id;
        }
      },
      'FuncStmt' => function (visitor\Path $path) use (&$declared) {
        $id = $path->node->name->symbol->id;
        if (!in_array($id, $declared)) {
          $declared[] = $id;
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
        $id = $path->node->name->symbol->id;
        if (in_array($id, $to_remove)) {
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
