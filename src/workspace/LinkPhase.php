<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\File;
use Cthulhu\Errors\Error;
use Cthulhu\ir\Linker;
use Cthulhu\ir\Lower;

class LinkPhase {
  private File $syntax_tree;

  function __construct(File $syntax_tree) {
    $this->syntax_tree = $syntax_tree;
  }

  /**
   * @return ResolvePhase
   * @throws Error
   */
  function link(): ResolvePhase {
    $lib     = Lower::file($this->syntax_tree);
    $ir_tree = Linker::link($lib);
    return new ResolvePhase($ir_tree);
  }
}
