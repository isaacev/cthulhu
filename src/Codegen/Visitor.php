<?php

namespace Cthulhu\Codegen;

class Visitor {
  static function walk(PHP\Node $node, array $callbacks): void {
    $path = new Path(null, $node);
    $table = new Table($callbacks);
    $path->walk($table);
  }

  static function edit(PHP\Node $node, array $callbacks): PHP\Node {
    $path = new Path(null, $node);
    $table = new Table($callbacks);
    return $path->edit($table);
  }

  /**
   * Given a starting node and an array that maps IR\Symbol ids => PHP\Node,
   * traverse the node replacing any references to symbols in the mapping with
   * the appropriate expression.
   */
  static function replace_references(PHP\Node $node, array $mapping): PHP\Node {
    $path = new Path(null, $node);
    $table = new Table([
      'VariableExpr' => function (Path $path) use (&$mapping) {
        $symbol_id = $path->node->variable->symbol->id;
        if (array_key_exists($symbol_id, $mapping)) {
          $path->replace_with($mapping[$symbol_id]);
        }
      },
    ]);
    return $path->edit($table);
  }
}
