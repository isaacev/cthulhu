<?php

namespace Cthulhu\lib\cli;

class Program {
  public $grammar;

  function __construct(string $name, string $version) {
    $this->grammar = new internals\ProgramGrammar($name, $version);
  }

  function bool_flag(string $name, string $description): self {
    [ $id, $short ] = self::parse_flag_name($name);
    $flag_grammar = new internals\BoolFlagGrammar($id, $short, $description);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  function short_circuit_flag(string $name, string $description, callable $callback): self {
    [ $id, $short ] = self::parse_flag_name($name);
    $flag_grammar = new internals\ShortCircuitFlagGrammar($id, $short, $description, $callback);
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
    $result  = $this->grammar->parse($scanner);

    if ($result->subcommand === null) {
      $flags = Lookup::from_flat_array($result->flags->flags);
      $result->grammar->dispatch($flags);
    } else {
      $result->subcommand->grammar->dispatch($result);
    }
  }

  protected static function parse_flag_name(string $name): array {
    if (preg_match('/^-([a-zA-Z0-9]) --(\S+)$/', $name, $match)) {
      return [ $match[2], $match[1] ];
    } else if (preg_match('/^--(\S+)$/', $name, $match)) {
      return [ $match[1], null ];
    } else {
      $fmt = 'cannot parse flag named `%s`';
      internals\Scanner::fatal_error($fmt, $name);
    }
  }
}
