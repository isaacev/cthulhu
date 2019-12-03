<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\File;
use Cthulhu\ir\Linker;
use Cthulhu\ir\Lower;
use Cthulhu\ir\Table;

class LinkPhase {
  private $syntax_tree;

  function __construct(File $syntax_tree) {
    $this->syntax_tree = $syntax_tree;
  }

  function link(): ResolvePhase {
    $spans   = new Table();
    $lib     = Lower::file($spans, $this->syntax_tree);
    $ir_tree = Linker::link($spans, $lib);
    return new ResolvePhase($spans, $ir_tree);
  }
}
