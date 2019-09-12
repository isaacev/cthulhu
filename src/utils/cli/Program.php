<?php

namespace Cthulhu\utils\cli;

class Program {
  public $grammar;

  function __construct(string $name, string $version) {
    $this->grammar = new internals\ProgramGrammar($name, $version);
  }

  function bool_flag(string $id, string $description): self {
    $flag_grammar = new internals\BoolFlagGrammar($id, $description);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  function short_circuit_flag(string $id, string $description, callable $callback): self {
    $flag_grammar = new internals\ShortCircuitFlagGrammar($id, $description, $callback);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  function subcommand(string $id, string $description): Subcommand {
    $subcommand = new Subcommand($this->grammar->name, $id, $description);
    $this->grammar->add_subcommand($subcommand->grammar);
    return $subcommand;
  }

  function callback(callable $callback): self {
    $this->grammar->add_callback($callback);
    return $this;
  }

  function parse(array $raw): void {
    $scanner = new internals\Scanner(array_slice($raw, 1));
    $result = $this->grammar->parse($scanner);

    if ($result->subcommand === null) {
      $flags = Lookup::from_flat_array($result->flags);
      $result->grammar->dispatch($flags);
    } else {
      $result->subcommand->grammar->dispatch($result);
    }
  }
}
