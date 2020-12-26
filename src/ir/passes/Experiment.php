<?php

/** @noinspection PhpUnused */

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes\Def;
use Cthulhu\ir\nodes\Module;
use Cthulhu\ir\nodes\Root;
use Cthulhu\lib\trees\Enter;
use Cthulhu\lib\trees\Leave;
use JetBrains\PhpStorm\Pure;

class Experiment {
  private int $level = 0;

  #[Enter(Root::class)] public function enter_root() {
    echo $this->indent() . "root {\n";
    $this->level++;
  }

  #[Leave(Root::class)] public function leave_root(Root|Module $node) {
    $this->level--;
    echo $this->indent() . "}\n";
  }

  #[Enter(Module::class)] public function enter_mod(Module $node) {
    if ($node->name) {
      $name = $node->name->text;
      echo $this->indent() . "mod $name {\n";
    } else {
      echo $this->indent() . "mod {\n";
    }

    $this->level++;
  }

  #[Leave(Module::class)] public function leave_mod() {
    $this->level--;
    echo $this->indent() . "}\n";
  }

  #[Enter(Def::class)] public function enter_def(Def $node) {
    $name = $node->name->text;
    echo $this->indent() . "fn $name {\n";
    $this->level++;
  }

  #[Leave(Def::class)] public function leave_def() {
    $this->level--;
    echo $this->indent() . "}\n";
  }

  #[Pure] private function indent(): string {
    return str_repeat('  ', $this->level);
  }
}
