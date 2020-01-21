<?php

namespace Cthulhu\lib\trees;

class Visitor {
  public static function walk(Nodelike $start, array $callbacks): void {
    $path      = new Path(null, $start);
    $callbacks = new CallbackTable($callbacks);
    self::_walk($path, $callbacks);
  }

  private static function _walk(Path $path, CallbackTable $callbacks): void {
    $callbacks->preorder($path->node, $path);
    foreach ($path->node->children() as $child) {
      if ($child !== null) {
        self::_walk($path->extend($child), $callbacks);
      }
    }
    $callbacks->postorder($path->node, $path);
  }

  public static function edit(EditableNodelike $start, array $callbacks): ?EditableNodelike {
    $path      = new EditablePath(null, $start);
    $callbacks = new CallbackTable($callbacks);
    return self::_edit($path, $callbacks);
  }

  private static function _edit(EditablePath $path, CallbackTable $callbacks): ?EditableNodelike {
    $prev_node = null;
    while ($prev_node !== $path->get_node() && $path->get_node()) {
      $prev_node = $path->get_node();
      $callbacks->preorder($path->get_node(), $path);
    }

    if ($path->get_node() === null) {
      return null;
    }

    $children_changed = false;
    $new_children     = [];
    foreach ($path->get_node()->children() as $child_node) {
      if ($child_node !== null) {
        $child_path = new EditablePath($path, $child_node);
        self::_edit($child_path, $callbacks);
        $children_changed = ($child_node !== $child_path->get_node()) || $children_changed;
        $new_children[]   = $child_path->get_node();
      } else {
        $new_children[] = null;
      }
    }

    if ($children_changed) {
      $path->set_node($path->get_node()->from_children($new_children));
    }

    $prev_node = null;
    while ($prev_node !== $path->get_node() && $path->get_node()) {
      $prev_node = $path->get_node();
      $callbacks->postorder($path->get_node(), $path);
    }

    return $path->get_node();
  }
}
