<?php

namespace Cthulhu\utils\cli\internals;

use \Cthulhu\utils\cli\Lookup;
use \Cthulhu\utils\fmt\StreamFormatter;

class ProgramGrammar extends HasFlagsGrammar {
  public $name;
  public $version;
  public $subcommand_grammars;
  public $callback;

  function __construct(string $name, string $version) {
    $this->name = $name;
    $this->version = $version;
    $this->subcommand_grammars = [];
    $this->callback = [$this, 'print_help'];

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
    $f = new StreamFormatter(STDOUT);
    Helper::usage($f, $this->name, '[FLAGS]', '[SUBCOMMAND]');
    $f->newline();
    Helper::section($f, 'flags', ...$this->flag_grammars);
    $f->newline();
    Helper::section($f, 'subcommands', ...$this->subcommand_grammars);
  }

  function print_version(): void {
    echo "$this->name $this->version\n";
  }

  function subcommand_completions(): array {
    $comps = [];
    foreach ($this->subcommand_grammars as $subcommand_grammar) {
      $comps[] = $subcommand_grammar->id;
    }
    return $comps;
  }

  function completions(): array {
    return array_merge(
      $this->flag_completions(),
      $this->subcommand_completions()
    );
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

  function complete_callback(Lookup $flags, Lookup $args) {
    $parts = $args->get('parts');
    if (empty($parts)) {
      echo implode(' ', $this->completions());
      exit(0);
    } else {
      $command_part = $parts[0];
      foreach ($this->subcommand_grammars as $grammar) {
        if ($grammar->id === $command_part) {
          echo implode(' ', $grammar->completions());
          echo PHP_EOL;
          exit(0);
        }
      }
      exit(1);
    }
  }

  function get_subcommand(string $token): ?SubcommandGrammar {
    foreach ($this->subcommand_grammars as $grammar) {
      if ($grammar->id === $token) {
        return $grammar;
      }
    }

    if ($token === '__complete') {
      $grammar = new SubcommandGrammar($this->name, '__complete', '');
      $grammar->add_argument(new VariadicArgumentGrammar('parts', ''));
      $grammar->add_callback([$this, 'complete_callback']);
      return $grammar;
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

    Scanner::fatal_error('unknown subcommand: `%s`', $token);
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
