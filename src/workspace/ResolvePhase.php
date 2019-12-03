<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\names\Resolve;
use Cthulhu\ir\nodes\Program;
use Cthulhu\ir\Table;

class ResolvePhase {
  private $spans;
  private $ir_tree;

  function __construct(Table $spans, Program $ir_tree) {
    $this->spans   = $spans;
    $this->ir_tree = $ir_tree;
  }

  function resolve(): CheckPhase {
    [ $name_to_symbol, $symbol_to_name ] = Resolve::names(
      $this->spans,
      $this->ir_tree
    );
    Resolve::validate($name_to_symbol, $this->ir_tree);
    return new CheckPhase(
      $this->spans,
      $name_to_symbol,
      $symbol_to_name,
      $this->ir_tree
    );
  }
}
