<?php

namespace Cthulhu\Types;

class FuncType extends Type {
  public $params;
  public $returns;

  function __construct(array $params, Type $returns) {
    $this->params = $params;
    $this->returns = $returns;
  }

  public function returns_something(): bool {
    if ($this->returns instanceof VoidType) {
      return false;
    } else {
      return true;
    }
  }

  public function accepts(Type $other): bool {
    if ($other instanceof FuncType) {
      if (count($this->params) !== count($other->params)) {
        return false;
      }

      for ($i = 0, $len = count($this->params); $i < $len; $i++) {
        if ($this->params[$i]->accepts($other->params[$i]) === false) {
          return false;
        }
      }

      return $this->returns->accepts($other->returns);
    } else {
      return false;
    }
  }

  public function __toString(): string {
    $one_param = count($this->params) === 1;
    $params = $one_param ? '(' : '';
    foreach ($this->params as $i => $param) {
      $params .= $i > 0 ? ", $param" : "$param";
    }
    $params .= $one_param ? ')' : '';
    return "$params -> $this->returns";
  }

  public function jsonSerialize() {
    return [
      'type' => 'FuncType'
    ];
  }
}
