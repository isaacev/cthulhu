<?php

namespace Cthulhu\lib\cli;

class Subcommand {
  public $grammar;

  function __construct(string $program_name, string $id, string $description) {
    $this->grammar = new internals\SubcommandGrammar($program_name, $id, $description);
  }

  function bool_flag(string $id, string $description): self {
    $flag_grammar = new internals\BoolFlagGrammar($id, $description);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  function str_flag(string $id, string $description, ?array $pattern = null) {
    $flag_grammar = new internals\StrFlagGrammar($id, $description, $pattern);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  function single_argument(string $id, string $description): self {
    $arg_grammar = new internals\SingleArgumentGrammar($id, $description);
    $this->grammar->add_argument($arg_grammar);
    return $this;
  }

  function variadic_argument(string $id, string $description): self {
    $arg_grammar = new internals\VariadicArgumentGrammar($id, $description);
    $this->grammar->add_argument($arg_grammar);
    return $this;
  }

  function callback(callable $callback): self {
    $this->grammar->add_callback($callback);
    return $this;
  }
}
