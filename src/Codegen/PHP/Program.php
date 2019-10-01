<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\{ Buildable, Builder };

class Program extends Node {
  public $modules;
  public $main_fn;

  function __construct(array $modules, Reference $main_fn) {
    $this->modules = $modules;
    $this->main_fn = $main_fn;
  }

  public function to_children(): array {
    return $this->modules;
  }

  public function from_children(array $nodes): Node {
    return new self($nodes, $this->main_fn);
  }

  public function build(): Builder {
    return (new Builder)
      ->opening_php_tag()
      ->newline()
      ->each($this->modules, (new Builder)
        ->newline()
        ->newline())
      ->newline()
      ->newline()
      ->keyword('namespace')
      ->space()
      ->brace_left()
      ->increase_indentation()
      ->newline_then_indent()
      ->then(new ReferenceExpr($this->main_fn))
      ->paren_left()
      ->paren_right()
      ->semicolon()
      ->decrease_indentation()
      ->newline_then_indent()
      ->brace_right()
      ->newline();
  }
}
