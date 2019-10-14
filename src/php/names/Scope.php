<?php

namespace Cthulhu\php\names;

class Scope {
  protected $names = [];
  protected $tmp_generator;

  function __construct() {
    $this->tmp_generator = self::generate_tmp_var_name();
  }

  public function use_name(string $name): void {
    $this->names[] = $name;
  }

  public function has_name(string $name): bool {
    return in_array($name, $this->names);
  }

  public function next_tmp_name(): string {
    $current = $this->tmp_generator->current();
    $this->tmp_generator->next();
    return $current;
  }

  private const alphabet = [
    'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
    'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
  ];

  static function generate_tmp_var_name() {
    $index = 0;
    $loops = 0;
    while (true) {
      if ($index >= count(self::alphabet)) {
        $index = 0;
        $loops++;
      }

      $letter = self::alphabet[$index++];
      $value = str_repeat($letter, $loops + 1);
      yield $value;
    }
  }
}

