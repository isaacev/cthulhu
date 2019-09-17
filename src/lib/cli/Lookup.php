<?php

namespace Cthulhu\lib\cli;

class Lookup {
  function __construct(array $table) {
    $this->table = $table;
  }

  function get(string $id, $fallback = null) {
    if (array_key_exists($id, $this->table)) {
      return $this->table[$id];
    } else {
      return $fallback;
    }
  }

  static function from_flat_array(array $list): self {
    $table = [];
    foreach ($list as $flag) {
      $table[$flag->id] = $flag->value;
    }
    return new self($table);
  }
}
