<?php

namespace Cthulhu\IR;

class Binding {
  public $kind;
  public $symbol;
  public $data;

  function __construct(string $kind, Symbol $symbol, $data) {
    $this->kind = $kind;
    $this->symbol = $symbol;
    $this->data = $data;
  }

  function matches_name(string $name): bool {
    return $this->symbol->name === $name;
  }

  function as_value(): Types\Type {
    if ($this->kind === 'value') {
      return $this->data;
    } else {
      throw new \Exception('expected value binding, was ' . $this->kind);
    }
  }

  function as_type(): Types\Type {
    if ($this->kind === 'type') {
      return $this->data;
    } else {
      throw new \Exception('expected type binding, was ' . $this->kind);
    }
  }

  function as_scope(): ModuleScope {
    if ($this->kind === 'module') {
      return $this->data;
    } else {
      throw new \Exception('expected module binding, was ' . $this->kind);
    }
  }

  static function for_value(Symbol $symbol, Types\Type $type) {
    return new self('value', $symbol, $type);
  }

  static function for_type(Symbol $symbol, Types\Type $type) {
    return new self('type', $symbol, $type);
  }

  static function for_module(ModuleScope $scope) {
    return new self('module', $scope->symbol, $scope);
  }
}
