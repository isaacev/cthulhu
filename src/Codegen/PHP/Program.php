<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\{ Buildable, Builder };

class Program implements Buildable {
  public $builtins;
  public $modules;
  public $main_fn;

  function __construct(array $builtins, array $modules, Reference $main_fn) {
    $this->builtins = $builtins;
    $this->modules = $modules;
    $this->main_fn = $main_fn;
  }

  public function build(): Builder {
    return (new Builder)
      ->opening_php_tag()
      ->newline()
      ->comment('builtin modules')
      ->newline()
      ->each($this->builtins)
      ->newline()
      ->newline()
      ->comment('user modules')
      ->newline()
      ->each($this->modules)
      ->newline()
      ->newline()
      ->comment('call to main function')
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
