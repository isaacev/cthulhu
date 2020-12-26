<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\nodes\Root;
use Cthulhu\ir\passes;

/**
 * Applies optimization passes to an IR tree. These optimizations include but
 * are not limited to:
 *
 * - Inlining some functions
 *
 * - Removing functions that aren't reachable from the program's entries
 *
 * - Removing modules that are empty (either because the module was written as
 *   empty or because all of the module's child functions or child modules have
 *   been removed).
 *
 * - Combine serial function calls into a single function call. For example
 *   this:
 *
 *   `a(1, 2)(3)`
 *
 *   Becomes this:
 *
 *   `a(1, 2, 3)`
 *
 *   This normalization will later help with arity analysis.
 *
 * @package Cthulhu\workspace
 */
class EarlyOptimizationPhase {
  private Root $tree;

  public function __construct(Root $tree) {
    $this->tree = $tree;
  }

  public function optimize(): CodegenPhase {

//    Visitor::walk2($this->tree, new passes\Experiment());

    $this->tree = passes\Inline::apply($this->tree);
    $this->tree = passes\ShakeTree::apply($this->tree);
    $this->tree = passes\CombineCalls::apply($this->tree);
    return new CodegenPhase($this->tree);
  }
}
