<?php

namespace Cthulhu\lib\trees;

class Visitor {
  public static function walk(Nodelike $start, array $callbacks): void {
    $path      = new Path(null, $start);
    $callbacks = new CallbackTable($callbacks);
    self::_walk($path, $callbacks);
  }

  private static function _walk(Path $path, CallbackTable $callbacks): void {
    $callbacks->preorder($path);
    foreach ($path->node->children() as $child) {
      if ($child !== null) {
        self::_walk($path->extend($child), $callbacks);
      }
    }
    $callbacks->postorder($path);
  }
}
