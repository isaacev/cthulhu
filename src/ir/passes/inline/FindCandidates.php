<?php

namespace Cthulhu\ir\passes\inline;

use Cthulhu\ir\nodes\Apply;
use Cthulhu\ir\nodes\Def;
use Cthulhu\ir\nodes\NameExpr;
use Cthulhu\lib\trees\Enter;
use Cthulhu\lib\trees\Leave;

class FindCandidates {
  /* @var Def[] */
  public array $candidates = [];
  private int|null $current_def_id = null;

  #[Enter(Def::class)] public function enter_def(Def $def) {
    $this->current_def_id = $def->name->symbol->get_id();

    if (self::is_candidate($def)) {
      $this->candidates[$this->current_def_id] = $def;
    }
  }

  #[Leave(Def::class)] public function leave_def() {
    $this->current_def_id = null;
  }

  #[Enter(Apply::class)] public function enter_apply(Apply $apply) {
    if ($apply->callee instanceof NameExpr) {
      $callee_id = $apply->callee->name->symbol->get_id();

      // If the callee symbol ID is equal to the ID of the current function
      // name ID, this call represents a recursive call site and so cannot
      // be inlined without causing infinite code expansion.
      if ($callee_id === $this->current_def_id) {
        unset($this->candidates[$callee_id]);
      }
    }
  }

  private static function is_candidate(Def $def): bool {
    return $def->body !== null && count($def->body) === 1;
  }
}
