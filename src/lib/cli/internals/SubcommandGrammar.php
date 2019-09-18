<?php

namespace Cthulhu\lib\cli\internals;

use \Cthulhu\lib\cli\Lookup;
use \Cthulhu\lib\fmt\StreamFormatter;

class SubcommandGrammar implements Describeable {
  public $program_name;
  public $id;
  public $description;
  public $flags_grammar;
  public $argument_grammars;
  public $callback;

  function __construct(string $program_name, string $id, string $description) {
    $this->program_name = $program_name;
    $this->id = $id;
    $this->description = $description;
    $this->flags_grammar = new FlagsGrammar();
    $this->argument_grammars = [];
    $this->callback = [$this, 'print_help'];

    $this->add_flag(new ShortCircuitFlagGrammar(
      'help',
      'Show this message',
      [$this, 'print_help']
    ));
  }

  function print_help(): void {
    $f = new StreamFormatter(STDOUT);
    Helper::usage($f, $this->program_name, $this->id, '[FLAGS]', ...$this->argument_grammars);
    $f->newline();
    Helper::section($f, 'flags', ...$this->flags_grammar->flags);
    $f->newline();
    Helper::section($f, 'arguments', ...$this->argument_grammars);
  }

  function completions(): array {
    return $this->flags_grammar->completions();
  }

  function full_name(): string {
    return $this->id;
  }

  function description(): string {
    return $this->description;
  }

  function add_flag(FlagGrammar $new_grammar): void {
    $this->flags_grammar->add($new_grammar);
  }

  function add_argument(ArgumentGrammar $new_grammar): void {
    foreach ($this->argument_grammars as $existing_grammar) {
      $both_are_variadic = (
        $existing_grammar instanceof VariadicArgumentGrammar &&
        $new_grammar instanceof VariadicArgumentGrammar
      );

      if ($both_are_variadic) {
        $fmt = 'cannot have more than 1 variadic arguments in the `%s` command';
        Scanner::fatal_error($fmt, $this->id);
      } else if ($existing_grammar->id === $new_grammar->id) {
        $fmt = 'cannot have multiple arguments named `%s` in the `%s` command';
        Scanner::fatal_error($fmt, $new_grammar->id, $this->id);
      }
    }

    $this->argument_grammars[] = $new_grammar;
  }

  function add_callback(callable $callback): void {
    $this->callback = $callback;
  }

  function parse_args(Scanner $scanner): array {
    $args = [];
    foreach ($this->argument_grammars as $grammar) {
      $args[] = $grammar->parse($scanner);
    }
    return $args;
  }

  function parse(ProgramGrammar $program, Scanner $scanner): SubcommandResult {
    $flags = $this->flags_grammar->parse($scanner);
    $args = $this->parse_args($scanner);
    return new SubcommandResult($this, $flags, $args);
  }

  function dispatch(ProgramResult $program_result) {
    $flags = Lookup::from_flat_array($program_result->subcommand->flags->flags);
    $args = Lookup::from_flat_array($program_result->subcommand->arguments);
    if ($this->callback) {
      call_user_func($this->callback, $flags, $args);
    }
  }
}