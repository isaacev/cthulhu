<?php

namespace Cthulhu\lib\cli;

class Subcommand {
  public internals\SubcommandGrammar $grammar;

  public function __construct(string $program_name, string $id, string $description) {
    $this->grammar = new internals\SubcommandGrammar($program_name, $id, $description);
  }

  /** @noinspection PhpUnused */
  public function short_circuit_flag(string $name, string $description, callable $callback): self {
    [ $id, $short ] = self::parse_flag_name($name);
    $flag_grammar = new internals\ShortCircuitFlagGrammar($id, $short, $description, $callback);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  /** @noinspection PhpUnused */
  public function bool_flag(string $name, string $description): self {
    [ $id, $short ] = self::parse_flag_name($name);
    $flag_grammar = new internals\BoolFlagGrammar($id, $short, $description);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  /** @noinspection PhpUnused */
  public function int_flag(string $name, string $description, string $arg_name): self {
    [ $id, $short ] = self::parse_flag_name($name);
    $flag_grammar = new internals\IntFlagGrammar($id, $short, $description, $arg_name);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  public function str_flag(string $name, string $description, string $arg_name, ?array $pattern = null): self {
    [ $id, $short ] = self::parse_flag_name($name);
    $flag_grammar = new internals\StrFlagGrammar($id, $short, $description, $arg_name, $pattern);
    $this->grammar->add_flag($flag_grammar);
    return $this;
  }

  /** @noinspection PhpUnused */
  public function single_argument(string $id, string $description): self {
    $arg_grammar = new internals\SingleArgumentGrammar($id, $description);
    $this->grammar->add_argument($arg_grammar);
    return $this;
  }

  /** @noinspection PhpUnused */
  public function optional_single_argument(string $id, string $description): self {
    $arg_grammar = new internals\OptionalSingleArgumentGrammar($id, $description);
    $this->grammar->add_argument($arg_grammar);
    return $this;
  }

  /** @noinspection PhpUnused */
  public function variadic_argument(string $id, string $description): self {
    $arg_grammar = new internals\VariadicArgumentGrammar($id, $description);
    $this->grammar->add_argument($arg_grammar);
    return $this;
  }

  /** @noinspection PhpUnused */
  public function callback(callable $callback): self {
    $this->grammar->add_callback($callback);
    return $this;
  }

  /** @noinspection PhpInconsistentReturnPointsInspection */
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
