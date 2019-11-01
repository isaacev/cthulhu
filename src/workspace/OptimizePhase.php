<?php

namespace Cthulhu\workspace;

use Cthulhu\php\nodes\Program;
use Cthulhu\php\passes\ConstFolding;
use Cthulhu\php\passes\Inline;
use Cthulhu\php\passes\TreeShaking;

class OptimizePhase {
  private $php_tree;

  function __construct(Program $php_tree) {
    $this->php_tree = $php_tree;
  }

  function optimize(array $passes = []): WritePhase {
    $all    = isset($passes['all'])    && $passes['all']    === true;
    $inline = isset($passes['inline']) && $passes['inline'] === true;
    $fold   = isset($passes['fold'])   && $passes['fold']   === true;
    $shake  = isset($passes['shake'])  && $passes['shake']  === true;

    if ($all || $inline) {
      $this->php_tree = Inline::apply($this->php_tree);
    }

    if ($all || $fold) {
      $this->php_tree = ConstFolding::apply($this->php_tree);
    }

    if ($all || $shake) {
      $this->php_tree = TreeShaking::apply($this->php_tree);
    }

    return new WritePhase($this->php_tree);
  }
}