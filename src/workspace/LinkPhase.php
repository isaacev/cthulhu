<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\File;
use Cthulhu\ir\Linker;
use Cthulhu\ir\Lower;

class LinkPhase {
  private File $syntax_tree;

  function __construct(File $syntax_tree) {
    $this->syntax_tree = $syntax_tree;
  }

  function link(): ResolvePhase {
    $lib     = Lower::file($this->syntax_tree);
    $ir_tree = Linker::link($lib);
    return new ResolvePhase($ir_tree);
  }
}
