<?php

namespace Cthulhu\php\visitor;

use Cthulhu\php;

class Visitor {
  public static function walk(php\nodes\Node $node, array $callbacks): void {
    $path  = new Path(null, $node);
    $table = new Table($callbacks);
    self::_walk($path, $table);
  }

  private static function _walk(Path $path, Table $table): void {
    $table->preorder($path);
    foreach ($path->node->to_children() as $child_node) {
      if ($child_node === null) {
        continue;
      }
      self::_walk(new Path($path, $child_node), $table);
    }
    $table->postorder($path);
  }

  public static function edit(php\nodes\Node $node, array $callbacks): php\nodes\Node {
    $path  = new Path(null, $node);
    $table = new Table($callbacks);
    self::_edit($path, $table);
    return $path->node;
  }

  private static function _edit(Path $path, Table $table): array {
    $table->preorder($path);
    $preorder_sibling_nodes = $path->new_sibling_nodes;
    if ($path->was_removed()) {
      // If the path was removed, immediately exit this call to the edit function
      // because all subsequent steps would be operating on a nonexistent node.
      return [];
    }

    // Child nodes that have yet to be recursed. If a child node adds siblings to
    // the tree, those nodes will be prepended to this array.
    $unedited_child_nodes = $path->node->to_children();

    // A list of child nodes that have been recursed.
    $edited_child_nodes = [];

    // If any child nodes change, this flag will be set to `true` which signals
    // that the current node needs to be rebuilt because its child references
    // are out-of-date. If this variable is still false after recursing through
    // all child nodes we can safely skip rebuilding the current node.
    $any_children_changed = false;

    // Keep a reference to the current node so that if the current node is
    // replaced by one of the child node callbacks, we can catch that change and
    // prevent recursing with stale nodes.
    $node_before_editing_children = $path->node;

    while (!empty($unedited_child_nodes)) {
      // The `array_shift` call can't be the loop expression because it's
      // possible that some of the unedited child nodes are `null` and if the
      // call returns `null` the loop will exit prematurely.
      $child_node = array_shift($unedited_child_nodes);
      if ($child_node === null) {
        $edited_child_nodes[] = null;
        continue;
      }

      $child_path = new Path($path, $child_node);

      // Recursively pass the child path to the edit method and capture any new
      // sibling nodes that the child path has added to the tree.
      $new_sibling_nodes = self::_edit($child_path, $table);

      if ($path->was_removed()) {
        // The child node removed one of its ancestors so stop the recursion.
        return [];
      } else if ($node_before_editing_children !== $path->node) {
        // The child node replaced the current node so restart editing.
        return array_merge(self::_edit($path, $table), $preorder_sibling_nodes);
      }

      if ($child_path->was_removed()) {
        // Remove the child node from its parent. Ignore any sibling nodes that
        // the removed child tried to add.
        $any_children_changed = true;
        continue;
      }

      // Add the edited child node to the list of other edited child nodes
      $edited_child_nodes[] = $child_path->node;
      $any_children_changed = $child_path->was_changed() || $any_children_changed;

      // If the child node added any siblings to the tree, those siblings have
      // not been edited yet so add those to the front of the unedited queue.
      if (!empty($new_sibling_nodes)) {
        array_unshift($unedited_child_nodes, ...$new_sibling_nodes);
        $any_children_changed = true;
      }
    }

    // If any children changed, rebuild the current node.
    if ($any_children_changed) {
      $path->replace_with($path->node->from_children($edited_child_nodes));
    }

    $table->postorder($path);

    if ($path->was_removed()) {
      // Check if the current node was removed in the postorder callback. If so,
      // ignore any sibling nodes it tried to add to the tree.
      return [];
    } else {
      return $path->new_sibling_nodes;
    }
  }

  /**
   * Given a starting node and an array that maps IR\Symbol ids => php\nodes\Node,
   * traverse the node replacing any references to symbols in the mapping with
   * the appropriate expression.
   *
   * @param php\nodes\Node $node
   * @param array          $mapping
   * @return php\nodes\Node
   */
  public static function replace_references(php\nodes\Node $node, array $mapping): php\nodes\Node {
    return self::edit($node, [
      'VariableExpr' => function (Path $path) use (&$mapping) {
        $symbol_id = $path->node->variable->symbol->get_id();
        if (array_key_exists($symbol_id, $mapping)) {
          $path->replace_with($mapping[$symbol_id]);
        }
      },
    ]);
  }
}
