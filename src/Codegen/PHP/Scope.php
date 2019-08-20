<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\IR\Symbol;

abstract class Scope {
  protected $symbol_to_uid = [];
  protected $uids = [];

  protected function generate_uid(string $prefix = 'tmp'): string {
    $count = 1;
    $uid = $prefix;
    while (in_array($uid, $this->uids)) {
      $count++;
      $uid = $prefix . $count;
    }
    array_push($this->uids, $uid);
    return $uid;
  }

  public function new_temporary(): string {
    return $this->generate_uid();
  }

  public function new_variable(Symbol $symbol, string $name): string {
    $uid = $this->generate_uid($name);
    $this->symbol_to_uid[$symbol->id] = $uid;
    return $uid;
  }

  public function get_variable(Symbol $symbol): string {
    $uid = $this->symbol_to_uid[$symbol->id];
    return $uid;
  }
}
