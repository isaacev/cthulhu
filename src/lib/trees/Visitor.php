<?php

namespace Cthulhu\lib\trees;

class Visitor {
  public static function walk(Nodelike $start, array $callbacks): void {
    $path      = new Path(null, $start);
    $callbacks = new CallbackTable($callbacks);
    self::_walk($path, $callbacks);
  }

  public static function walk2(Nodelike $start, mixed $instance): void {
    $path  = new Path(null, $start);
    $table = new AttrTable($instance);
    self::_walk($path, $table);
  }

  private static function _walk(Path $path, LookupTable $callbacks): void {
    $callbacks->preorder($path->node, $path);
    if (!$path->is_recursion_aborted()) {
      foreach ($path->node->children() as $child) {
        if ($child !== null) {
          self::_walk($path->extend($child), $callbacks);
        }
      }
    }
    $callbacks->postorder($path->node, $path);

    /**
     * If the current node has a successor, walk the successor *after* handling
     * any postorder callbacks for the current node.
     */
    if ($path->node instanceof HasSuccessor) {
      $next = $path->node->successor();
      if ($next !== null) {
        self::_walk(new Path($path->parent, $next), $callbacks);
      }
    }
  }

  /**
   * This method exposes an algorithm for the traversal and modification of a
   * persistent tree structure. Given a mapping of node queries to callbacks,
   * perform a depth-first traversal of the tree executing relevant callbacks at
   * each node based on which queries match the current node. Queries can specify
   * whether they should be evaluated when the visitor enters a node before
   * processing any child nodes (equivalent to a pre-order traversal) or when
   * the visitor exits the node after processing all child nodes (equivalent to
   * a post-order traversal).
   *
   * When a callback matches a node, the callback is allowed to make one of the
   * following changes to the tree:
   *
   * - Replace the node with another node from outside of the tree
   * - Remove the node with no replacement
   *
   * If neither of the changes are performed, the node does not change.
   *
   * In the case where a node is either removed or replaced, the original tree
   * data structure is not mutated but a new tree data structure is generated.
   * The new data structure re-uses as many nodes as possible from the original
   * tree to reduce the number of new objects that are created.
   *
   * While this method is relatively short, the main implementation of the
   * algorithm is inside the {@link Visitor::_edit} method.
   *
   * The tree traversal & edit algorithm assumes that changes will only ever
   * happen to the current node via the {@link EditablePath::remove} and
   * {@link EditablePath::replace}. Any changes to the tree outside of that
   * interface will likely cause bugs.
   *
   * @param EditableNodelike $start
   * @param array            $callbacks
   * @return EditableNodelike|null
   * @see Visitor::_edit
   */
  public static function edit(EditableNodelike $start, array $callbacks): ?EditableNodelike {
    $path      = new EditablePath(null, $start);
    $callbacks = new CallbackTable($callbacks);
    return self::_edit($path, $callbacks);
  }

  /**
   * @param EditablePath $curr_path
   * @param LookupTable  $callbacks
   * @return EditableNodelike|null
   * @see Visitor::edit
   */
  private static function _edit(EditablePath $curr_path, LookupTable $callbacks): ?EditableNodelike {
    do {
      do {
        // If the current node was removed and no replacement was given, exit
        // the function because the entire subtree is no longer reachable.
        if ($curr_path->get_node() === null) {
          goto done;
        }

        // Perform any preorder callbacks on the current node. If the current node
        // is removed by any of the callbacks, perform any preorder callbacks on the
        // replacement node. Keep performing preorder callbacks until the node
        // stabilizes (doesn't change after applying the callbacks).
        $original_node = $curr_path->get_node();
        $callbacks->preorder($curr_path->get_node(), $curr_path);

        if ($curr_path->is_recursion_aborted()) {
          goto done;
        }
      } while ($original_node !== $curr_path->get_node());

      // Recurse into any child nodes, keeping track of whether any of the child
      // nodes were changed.
      $children_have_changed = false;

      // An array that will be populated with references to all child nodes
      // _after_ the child nodes have been recursively processed. If a child
      // node is already null or was deleted, add a null to the `$new_children`
      // array. If a child node wasn't changed, still add it to the `$new_children`
      // array because a sibling node might change, requiring previous nodes to
      // already be in the `$new_children` array.
      $new_children = [];
      foreach ($curr_path->get_node()->children() as $child_node) {
        if ($child_node === null) {
          // Don't perform any callbacks on NULL children but add a NULL value
          // to the `$new_children` array to maintain tree shape.
          $new_children[] = null;
          continue;
        }

        // Recurse using the child node and child path.
        $child_path = new EditablePath($curr_path, $child_node);
        self::_edit($child_path, $callbacks);

        // If the child node was modified (either by a callback or one of its own
        // children) set the `$children_have_changed` to true. This will signal
        // that the current node will have to be rebuilt once all of the child
        // nodes have been recursively edited.
        $children_have_changed = (
          ($child_node !== $child_path->get_node()) ||
          $children_have_changed
        );

        // Add the child node to the `$new_children` array. This array is used
        // when rebuilding the current node (if necessary).
        $new_children[] = $child_path->get_node();
      }

      // If any of the child nodes changed, rebuild the current node.
      if ($children_have_changed) {
        $new_curr_node = $curr_path->get_node()->from_children($new_children);
        $curr_path->set_node($new_curr_node);
      }

      // Perform any postorder callbacks on the current node. If the current node
      // is removed by any of the callbacks, return to the top of this function
      // and perform any preorder callbacks and any child node callbacks on the
      // replacement node. Keep looping over the preorder callbacks, child node
      // callbacks, and postorder callbacks until the current node is either
      // removed or isn't replaced.
      $original_node = $curr_path->get_node();
      $callbacks->postorder($curr_path->get_node(), $curr_path);
    } while ($original_node !== $curr_path->get_node());

    // Some nodes define a 'successor' node. A successor is different than a
    // child node because it is evaluated _after_ the parent's postorder callbacks
    // are evaluated.
    if ($curr_path->get_node() instanceof EditableSuccessor) {
      /** @noinspection PhpPossiblePolymorphicInvocationInspection */
      $succ_node = $curr_path->get_node()->successor();
      if ($succ_node === null) {
        goto done;
      }

      $succ_path = new EditablePath($curr_path, $succ_node);
      self::_edit($succ_path, $callbacks);

      $succ_has_changed = $succ_node !== $succ_path->get_node();

      // If the successor node changed, rebuild the current node. Because the
      // successor node has already been processed there is no reason to loop like
      // after the postorder for the current node.
      if ($succ_has_changed) {
        /* @var EditableSuccessor|null $succ_node */
        $succ_node = $succ_path->get_node();
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $curr_node = $curr_path->get_node()->from_successor($succ_node);
        $curr_path->set_node($curr_node);
      }
    }

    done:
    return $curr_path->get_node();
  }
}
