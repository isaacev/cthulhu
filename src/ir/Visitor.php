<?php

namespace Cthulhu\ir;

class Visitor {
  static function walk(nodes\Node $start, array $callbacks): void {
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

class CallbackTable {
  private $callbacks = [];

  function __construct(array $callbacks) {
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

  function preorder(Path $path) {
    foreach (self::get_node_kinds($path->node) as $kind) {
      if (array_key_exists($kind, $this->callbacks)) {
        if (array_key_exists('enter', $this->callbacks[$kind])) {
          $this->callbacks[$kind]['enter']($path->node, $path);
        }
      }
    }
  }

  function postorder(Path $path) {
    foreach (self::get_node_kinds($path->node) as $kind) {
      if (array_key_exists($kind, $this->callbacks)) {
        if (array_key_exists('exit', $this->callbacks[$kind])) {
          $this->callbacks[$kind]['exit']($path->node, $path);
        }
      }
    }
  }

  private static function get_node_kinds(nodes\Node $node): array {
    $parents   = array_values(class_parents($node));
    $child     = get_class($node);
    $hierarchy = array_merge([ $child ], $parents);
    return array_map(function ($classname) {
      return str_replace(__NAMESPACE__ . '\\nodes\\', '', $classname);
    }, $hierarchy);
  }
}
