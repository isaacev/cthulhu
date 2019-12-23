<?php

namespace Cthulhu\ir\types\traits;

use Cthulhu\ir\types\Type;

trait DefaultTransform {
  public function transform(callable $callback): ?Type {
    $this_after            = $callback($this);
    $children_before       = ($this_after ?? $this)->to_children();
    $children_have_changed = false;
    $children_after        = [];

    foreach ($children_before as $child_before) {
      assert($child_before instanceof Type);

      $child_after           = $child_before->transform($callback);
      $children_have_changed = $child_after || $children_have_changed;
      $children_after[]      = $child_after ?? $child_before;
    }

    if ($children_have_changed) {
      return ($this_after ?? $this)->from_children($children_after);
    } else {
      return $this_after;
    }
  }

  public function compare(Type $other, callable $callback): void {
    $callback($this, $other);
    if (get_class($this) === get_class($other)) {
      $this_children  = $this->to_children();
      $other_children = $other->to_children();
      foreach (array_map(null, $this_children, $other_children) as [$this_child, $other_child]) {
        /**
         * @var Type $this_child
         * @var Type $other_child
         */
        $this_child->compare($other_child, $callback);
      }
    }
  }

  public function compare_and_transform(Type $other, callable $callback): ?Type {
    $this_after = $callback($this, $other);
    if ($this_after === null && get_class($this) === get_class($other)) {
      $this_children_before  = $this->to_children();
      $other_children_before = $other->to_children();
      $children_have_changed = false;
      $this_children_after   = [];
      foreach ($this_children_before as $index => $this_child_before) {
        $other_child_before = $other_children_before[$index];
        assert($this_child_before instanceof Type);
        assert($other_child_before instanceof Type);
        $this_child_after      = $this_child_before->compare_and_transform($other_child_before, $callback);
        $children_have_changed = $this_child_after || $children_have_changed;
        $this_children_after[] = $this_child_after ?? $this_child_before;
      }

      if ($children_have_changed) {
        return $this->from_children($this_children_after);
      }
      return null;
    }
    return $this_after;
  }
}
