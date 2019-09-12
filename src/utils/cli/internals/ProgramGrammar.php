<?php

namespace Cthulhu\utils\cli\internals;

class ProgramGrammar extends HasFlagsGrammar {
  public $name;
  public $version;
  public $subcommand_grammars;
  public $callback;

  function __construct(string $name, string $version) {
    $this->name = $name;
    $this->version = $version;
    $this->subcommand_grammars = [];
    $this->callback = function () { echo "do nothing\n"; };

    parent::add_flag(new ShortCircuitFlagGrammar(
      'help',
      'Show this message',
      [$this, 'print_help']
    ));

    parent::add_flag(new ShortCircuitFlagGrammar(
      'version',
      'Show version number',
      [$this, 'print_version']
    ));
  }

  function print_help(): void {
    Helper::usage($this->name, '[FLAGS]', '[SUBCOMMAND]');
    Helper::section('flags', ...$this->flag_grammars);
    Helper::section('subcommands', ...$this->subcommand_grammars);
  }

  function print_version(): void {
    echo "$this->name $this->version\n";
  }

  function add_subcommand(SubcommandGrammar $new_grammar): void {
    foreach ($this->subcommand_grammars as $existing_grammar) {
      if ($existing_grammar->id === $new_grammar->id) {
        $fmt = 'cannot have multiple subcommands named `%s`';
        Scanner::fatal_error($fmt, $new_grammar->id);
      }
    }

    $this->subcommand_grammars[] = $new_grammar;
  }

  function get_subcommand(string $token): ?SubcommandGrammar {
    foreach ($this->subcommand_grammars as $grammar) {
      if ($grammar->id === $token) {
        return $grammar;
      }
    }
    return null;
  }

  function add_callback(callable $callback): void {
    $this->callback = $callback;
  }

  function parse_subcommand(Scanner $scanner) {
    if ($scanner->is_empty()) {
      return null;
    }

    $token = $scanner->advance();
    if ($grammar = $this->get_subcommand($token)) {
      return $grammar->parse($this, $scanner);
    }

    fatal('unknown subcommand: `%s`', $token);
  }

  function parse(Scanner $scanner): ProgramResult {
    $flags = $this->parse_flags($scanner);
    $subcommand = $this->parse_subcommand($scanner);
    return new ProgramResult($this, $flags, $subcommand);
  }

  function dispatch(\Cthulhu\utils\cli\Lookup $flags) {
    if ($this->callback) {
      call_user_func($this->callback, $flags);
    }
  }
}
