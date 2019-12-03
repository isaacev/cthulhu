<?php

namespace Cthulhu\php\visitor;

class Table {
  protected $postorder_callbacks = [];
  protected $preorder_callbacks = [];

  function __construct(array $callbacks) {
    foreach ($callbacks as $selector => $callback) {
      [ $order, $node_names ] = self::parse_selector($selector);
      if ($order === 'postorder') {
        foreach ($node_names as $node_name) {
          $this->postorder_callbacks[$node_name] = $callback;
        }
      } else {
        foreach ($node_names as $node_name) {
          $this->preorder_callbacks[$node_name] = $callback;
        }
      }
    }
  }

  public function preorder(Path $path): void {
    $name = self::get_node_name($path);
    if (array_key_exists($name, $this->preorder_callbacks)) {
      $this->preorder_callbacks[$name]($path);
    }
  }

  public function postorder(Path $path): void {
    $name = self::get_node_name($path);
    if (array_key_exists($name, $this->postorder_callbacks)) {
      $this->postorder_callbacks[$name]($path);
    }
  }

  protected static function get_node_name(Path $path): string {
    return str_replace('Cthulhu\\php\\nodes\\', '', get_class($path->node));
  }

  protected const SIMPLE_SELECTOR  = '/^(\w+)$/';
  protected const COMPLEX_SELECTOR = '/^(pre|post)order\((\w+(?:\|\w+)*)\)$/';

  protected static function parse_selector(string $sel): array {
    if (preg_match(self::SIMPLE_SELECTOR, $sel, $simple_match)) {
      return [
        'preorder',
        [ $simple_match[1] ],
      ];
    } else if (preg_match(self::COMPLEX_SELECTOR, $sel, $complex_match)) {
      return [
        $complex_match[1] . 'order',
        explode('|', $complex_match[2]),
      ];
    } else {
      throw new \Exception('unknown node selector');
    }
  }
}
