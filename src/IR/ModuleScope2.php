<?php

namespace Cthulhu\IR;

class ModuleScope2 implements Scope2 {
  public static function from_array(string $name, array $arr) {
    $scope = new self(null, $name);
    foreach ($arr as $name => $type) {
      $scope->add_name($name, new Symbol2($scope, $name));
    }
    return $scope;
  }

  public $parent;
  public $symbol;
  public $name;
  public $names;

  function __construct(?ModuleScope2 $parent, string $name = 'main') {
    $this->parent = $parent;
    $this->symbol = new Symbol2($parent, $name);
    $this->name = $name;
    $this->names = [];
  }

  function add_name(string $name, Symbol2 $symbol) {
    $this->names[$name] = $symbol;
  }

  function has_name(string $name): bool {
    return array_key_exists($this->names, $name);
  }

  function get_name(string $name): Symbol2 {
    return $this->names[$name];
  }
}
