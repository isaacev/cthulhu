<?php

namespace Cthulhu\Codegen\Optimizations;

use Cthulhu\Codegen\{ Path, PHP, Visitor };

class TreeShaking {
  public static function apply(PHP\Program $prog): PHP\Program {
    $declared = [];
    $used = [ $prog->main_fn->symbol->id ];

    Visitor::walk($prog, [
      'ReferenceExpr' => function (Path $path) use (&$used) {
        $id = $path->node->reference->symbol->id;
        if (!in_array($id, $used)) {
          $used[] = $id;
        }
      },
      'FuncStmt' => function (Path $path) use (&$declared) {
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

    return Visitor::edit($prog, [
      'FuncStmt' => function (Path $path) use (&$to_remove) {
        $id = $path->node->name->symbol->id;
        if (in_array($id, $to_remove)) {
          $path->remove();
        }
      },
      'postorder(NamespaceNode)' => function (Path $path) {
        if ($path->node->block->is_empty()) {
          $path->remove();
        }
      },
    ]);
  }
}
