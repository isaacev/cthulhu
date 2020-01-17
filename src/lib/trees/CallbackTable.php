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

  public function preorder(Path $path) {
    foreach (self::get_node_kinds($path->node) as $kind) {
      if (array_key_exists($kind, $this->callbacks)) {
        if (array_key_exists('enter', $this->callbacks[$kind])) {
          $this->callbacks[$kind]['enter']($path->node, $path);
        }
      }
    }
  }

  public function postorder(Path $path) {
    foreach (self::get_node_kinds($path->node) as $kind) {
      if (array_key_exists($kind, $this->callbacks)) {
        if (array_key_exists('exit', $this->callbacks[$kind])) {
          $this->callbacks[$kind]['exit']($path->node, $path);
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
