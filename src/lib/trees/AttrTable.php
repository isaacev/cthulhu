<?php

namespace Cthulhu\lib\trees;

use ReflectionClass;

class AttrTable implements LookupTable {
  private array $on_enter = [];
  private array $on_leave = [];

  public function __construct(object $instance) {
    $mirror = new ReflectionClass($instance);
    foreach ($mirror->getMethods() as $method) {
      $line    = $method->getStartLine();
      $file    = $method->getFileName();
      $closure = $method->getClosure($instance);

      foreach ($method->getAttributes() as $unparsed) {
        if ($attr = Enter::matches($unparsed, $file, $line)) {
          foreach ($attr->classes as $class) {
            if (array_key_exists($class, $this->on_enter)) {
              array_push($this->on_enter[$class], $closure);
            } else {
              $this->on_enter[$class] = [ $closure ];
            }
          }
        } else if ($attr = Leave::matches($unparsed, $file, $line)) {
          foreach ($attr->classes as $class) {
            if (array_key_exists($class, $this->on_leave)) {
              array_push($this->on_leave[$class], $closure);
            } else {
              $this->on_leave[$class] = [ $closure ];
            }
          }
        }
      }
    }
  }

  public function preorder(Nodelike $node, mixed ...$args): void {
    if (array_key_exists($node::class, $this->on_enter)) {
      $closures = $this->on_enter[$node::class];
      foreach ($closures as $closure) {
        $closure($node, ...$args);
      }
    }
  }

  public function postorder(Nodelike $node, mixed ...$args): void {
    if (array_key_exists($node::class, $this->on_leave)) {
      $closures = $this->on_leave[$node::class];
      foreach ($closures as $closure) {
        $closure($node, ...$args);
      }
    }
  }
}
