<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\nodes\File;
use Cthulhu\err\Error;
use Cthulhu\ir\Linker;
use Cthulhu\ir\Lower;

class LinkPhase {
  private File $syntax_tree;

  public function __construct(File $syntax_tree) {
    $this->syntax_tree = $syntax_tree;
  }

  /**
   * @return ResolvePhase
   * @throws Error
   */
  public function link(): ResolvePhase {
    $lib     = Lower::file($this->syntax_tree);
    $ir_tree = Linker::link($lib);
    return new ResolvePhase($ir_tree);
  }
}
