<?php

namespace Cthulhu\lib\cli;

use Cthulhu\lib\cli\internals\ArgumentResult;
use Cthulhu\lib\cli\internals\FlagResult;

class Lookup {
  protected array $table;

  public function __construct(array $table) {
    $this->table = $table;
  }

  /**
   * @param string $id
   * @param null   $fallback
   * @return mixed|null
   */
  public function get(string $id, $fallback = null) {
    if (array_key_exists($id, $this->table) && !empty($this->table[$id])) {
      return end($this->table[$id]);
    } else {
      return $fallback;
    }
  }

  public function get_all(string $id, $fallback = null) {
    if (array_key_exists($id, $this->table)) {
      return $this->table[$id];
    } else {
      return $fallback;
    }
  }

  /**
   * @param FlagResult[]|ArgumentResult[] $list
   * @return self
   */
  public static function from_flat_array(array $list): self {
    $table = [];
    foreach ($list as $flag) {
      if (array_key_exists($flag->id, $table)) {
        $table[$flag->id][] = $flag->value;
      } else {
        $table[$flag->id] = [ $flag->value ];
      }
    }
    return new self($table);
  }
}
