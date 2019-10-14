<?php

namespace Cthulhu;

class Workspace {
  public $file            = null;
  public $syntax_tree     = null;
  public $ir_tree         = null;
  public $php_tree        = null;
  public $ir_node_to_span = null;
  public $name_to_symbol  = null;
  public $symbol_to_name  = null;
  public $expr_to_type    = null;
  public $symbol_to_type  = null;

  public function open(string $filepath): self {
    $contents = @file_get_contents($filepath);
    if ($contents === false) {
      throw Errors::unable_to_read_file($filepath);
    }
    $this->file = new \Cthulhu\Source\File($filepath, $contents);
    return $this;
  }

  public function file(\Cthulhu\Source\File $file): self {
    $this->file = $file;
    return $this;
  }

  public function parse(): self {
    $this->syntax_tree = \Cthulhu\Parser\Parser::file_to_ast($this->file);
    return $this;
  }

  public function link(): self {
    $spans = $this->ir_node_to_span = new \Cthulhu\ir\Table();
    $lib = \Cthulhu\ir\Lower::file($spans, $this->syntax_tree);
    $this->ir_tree = \Cthulhu\ir\Linker::link($spans, $lib);
    return $this;
  }

  public function resolve(): self {
    list($name_to_symbol, $symbol_to_name) = \Cthulhu\ir\names\Resolve::names(
      $this->ir_node_to_span,
      $this->ir_tree
    );
    $this->name_to_symbol = $name_to_symbol;
    $this->symbol_to_name = $symbol_to_name;
    return $this;
  }

  public function check(): self {
    list($symbol_to_type, $expr_to_type) = \Cthulhu\ir\types\Check::types(
      $this->ir_node_to_span,
      $this->name_to_symbol,
      $this->symbol_to_name,
      $this->ir_tree
    );
    $this->symbol_to_type = $symbol_to_type;
    $this->expr_to_type = $expr_to_type;
    return $this;
  }

  public function codegen(): self {
    $this->php_tree = \Cthulhu\php\Generate::from(
      $this->name_to_symbol,
      $this->symbol_to_name,
      $this->symbol_to_type,
      $this->expr_to_type,
      $this->ir_tree
    );
    return $this;
  }

  public function write(): string {
    return $this->php_tree->build()->write(new \Cthulhu\lib\fmt\StringFormatter());
  }
}
