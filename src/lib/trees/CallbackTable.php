<?php

namespace Cthulhu\lib\trees;

class CallbackTable {
  private array $callbacks = [];

  public function __construct(array $callbacks) {
    foreach ($callbacks as $selector => $callback) {
      $direction = 'enter';
      if (preg_match('/^enter\((.+)\)$/', $selector, $matches)) {
        $direction = 'enter';
        $selector  = $matches[1];
      } else if (preg_match('/^exit\((.+)\)$/', $selector, $matches)) {
        $direction = 'exit';
        $selector  = $matches[1];
      }

      $kinds = explode('|', $selector);
      foreach ($kinds as $kind) {
        if (array_key_exists($kind, $this->callbacks)) {
          $this->callbacks[$kind][$direction] = $callback;
        } else {
          $this->callbacks[$kind] = [ $direction => $callback ];
        }
      }
    }
  }

  /**
   * Pass the current node (and any arguments) to all callbacks that are
   * registered for the node's class or any of its parent classes. Queries are
   * matched from starting with the most general class name (ex: Node) and
   * ending with the most specific class name (ex: BinaryExpr). *Note:* This
   * order is opposite of the order in {@link CallbackTable::postorder}.
   *
   * @param Nodelike $node
   * @param mixed    ...$args
   */
  public function preorder(Nodelike $node, ...$args) {
    foreach (array_reverse(self::get_node_kinds($node)) as $kind) {
      if (array_key_exists($kind, $this->callbacks)) {
        if (array_key_exists('enter', $this->callbacks[$kind])) {
          $this->callbacks[$kind]['enter']($node, ...$args);
        }
      }
    }
  }

  /**
   * Pass the current node (and any arguments) to all callbacks that are
   * registered for the node's class or any of its parent classes. Queries are
   * matched starting from the most specific class name (ex: BinaryExpr) and
   * ending with the most general class name (ex: Node). *Note:* This order is
   * opposite of the order in {@link CallbackTable::preorder}.
   *
   * @param Nodelike $node
   * @param mixed    ...$args
   */
  public function postorder(Nodelike $node, ...$args) {
    foreach (self::get_node_kinds($node) as $kind) {
      if (array_key_exists($kind, $this->callbacks)) {
        if (array_key_exists('exit', $this->callbacks[$kind])) {
          $this->callbacks[$kind]['exit']($node, ...$args);
        }
      }
    }
  }

  private static function get_node_kinds(Nodelike $node): array {
    $parents   = array_values(class_parents($node));
    $child     = get_class($node);
    $hierarchy = array_merge([ $child ], $parents);
    return array_map(function ($classname) {
      $parts = explode('\\', $classname);
      return end($parts);
    }, $hierarchy);
  }
}
