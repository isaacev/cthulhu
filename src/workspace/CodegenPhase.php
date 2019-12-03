<?php

namespace Cthulhu\workspace;

use Cthulhu\ir\nodes\Program;
use Cthulhu\ir\Table;
use Cthulhu\php\Lower;

class CodegenPhase {
  private $name_to_symbol;
  private $symbol_to_name;
  private $symbol_to_type;
  private $expr_to_type;
  private $ir_tree;

  function __construct(
    Table $name_to_symbol,
    Table $symbol_to_name,
    Table $symbol_to_type,
    Table $expr_to_type,
    Program $ir_tree
  ) {
    $this->name_to_symbol = $name_to_symbol;
    $this->symbol_to_name = $symbol_to_name;
    $this->symbol_to_type = $symbol_to_type;
    $this->expr_to_type   = $expr_to_type;
    $this->ir_tree        = $ir_tree;
  }

  function codegen(): OptimizePhase {
    $php_tree = Lower::from(
      $this->name_to_symbol,
      $this->symbol_to_name,
      $this->symbol_to_type,
      $this->expr_to_type,
      $this->ir_tree
    );
    return new OptimizePhase($php_tree);
  }
}
