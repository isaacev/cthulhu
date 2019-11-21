<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\Flow;
use Cthulhu\ir\nodes\Program;
use Cthulhu\ir\Table;
use Cthulhu\ir\types\Check;

class CheckPhase {
  private $spans;
  private $name_to_symbol;
  private $symbol_to_name;
  private $ir_tree;

  function __construct(
    Table $spans,
    Table $name_to_symbol,
    Table $symbol_to_name,
    Program $ir_tree
  ) {
    $this->spans = $spans;
    $this->name_to_symbol = $name_to_symbol;
    $this->symbol_to_name = $symbol_to_name;
    $this->ir_tree = $ir_tree;
  }

  function check(): CodegenPhase {
    list($symbol_to_type, $expr_to_type) = Check::types(
      $this->spans,
      $this->name_to_symbol,
      $this->symbol_to_name,
      $this->ir_tree
    );
    Flow::analyze($this->spans, $expr_to_type, $this->ir_tree);
    return new CodegenPhase(
      $this->name_to_symbol,
      $this->symbol_to_name,
      $symbol_to_type,
      $expr_to_type,
      $this->ir_tree
    );
  }
}
