<?php

namespace Cthulhu\lib\cli;

class Lookup {
  protected $table;

  function __construct(array $table) {
    $this->table = $table;
  }

  function get(string $id, $fallback = null) {
    if (array_key_exists($id, $this->table) && !empty($this->table[$id])) {
      return end($this->table[$id]);
    } else {
      return $fallback;
    }
  }

  function get_all(string $id, $fallback = null) {
    if (array_key_exists($id, $this->table)) {
      return $this->table[$id];
    } else {
      return $fallback;
    }
  }

  static function from_flat_array(array $list): self {
    $table = [];
    foreach ($list as $flag) {
      if (\array_key_exists($flag->id, $table)) {
        $table[$flag->id][] = $flag->value;
      } else {
        $table[$flag->id] = [ $flag->value ];
      }
    }
    return new self($table);
  }
}
