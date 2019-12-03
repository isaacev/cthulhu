<?php

namespace Cthulhu\lib\cli\internals;

use Cthulhu\lib\cli\Lookup;
use Cthulhu\lib\fmt\StreamFormatter;

class ProgramGrammar {
  public $name;
  public $version;
  public $flags_grammar;
  public $subcommand_grammars;
  public $callback;

  function __construct(string $name, string $version) {
    $this->name                = $name;
    $this->version             = $version;
    $this->flags_grammar       = new FlagsGrammar();
    $this->subcommand_grammars = [];
    $this->callback            = [ $this, 'print_help' ];

    $this->flags_grammar->add(new ShortCircuitFlagGrammar(
      'help',
      'h',
      'Show this message',
      [ $this, 'print_help' ]
    ));

    $this->flags_grammar->add(new ShortCircuitFlagGrammar(
      'version',
      'v',
      'Show version number',
      [ $this, 'print_version' ]
    ));
  }

  function print_help(): void {
    $f = new StreamFormatter(STDOUT);
    Helper::usage($f, $this->name, '[FLAGS]', '[SUBCOMMAND]');
    $f->newline();
    Helper::section($f, 'flags', ...$this->flags_grammar->flags);
    $f->newline();
    Helper::section($f, 'subcommands', ...$this->subcommand_grammars);
  }

  function print_version(): void {
    echo "$this->name $this->version\n";
  }

  function print_completion_script(): void {
    echo file_get_contents(realpath(__DIR__ . '/completion.sh'));
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
      $this->flags_grammar->completions(),
      $this->subcommand_completions()
    );
  }

  function add_flag(FlagGrammar $flag): void {
    $this->flags_grammar->add($flag);
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
    if (
      !isset($_ENV['COMP_CWORD']) ||
      !isset($_ENV['COMP_LINE']) ||
      !isset($_ENV['COMP_POINT'])
    ) {
      $this->print_completion_script();
      exit(0);
    }

    // command line input (with prompt and cursor on zsh): "> cthulhu foo b|ar"
    $w             = (int)$_ENV['COMP_CWORD'];   // 2
    $words         = $args->get('parts', []);    // [ "cthulhu", "foo", "bar" ]
    $word          = $words[$w];                 // "bar"
    $line          = $_ENV['COMP_LINE'];         // "cthulhu foo bar"
    $point         = (int)$_ENV['COMP_POINT'];   // 17 (pretty sure this is a zsh 5.3 bug)
    $partial_line  = substr($line, 0, $point);   // "cthulhu foo bar"
    $partial_words = array_slice($words, 0, $w); // [ "cthulhu", "foo" ]

    fprintf(STDERR, "w:             %d\n", $w);
    fprintf(STDERR, "words:         [ %s ]\n", implode(', ', $words));
    fprintf(STDERR, "word:          %s\n", $word);
    fprintf(STDERR, "line:          %s\n", $line);
    fprintf(STDERR, "point:         %d\n", $point);
    fprintf(STDERR, "partial_line:  %s\n", $partial_line);
    fprintf(STDERR, "partial_words: %s\n", implode(', ', $partial_words));

    // Determine where in the last word the cursor point is in
    $partial_word = $words[$w];
    $i            = strlen($partial_word);
    while (substr($partial_word, 0, $i) !== substr($partial_line, -1 * $i) && $i > 0) {
      $i--;
    }
    $partial_word    = substr($partial_word, 0, $i);
    $partial_words[] = $partial_word;
    fprintf(STDERR, "partial_word:  %s\n", $partial_word);
    fprintf(STDERR, "partial_words: %s\n", implode(', ', $partial_words));

    $parts     = array_slice($partial_words, 1);
    $last_part = end($parts);
    if ($last_part !== '') {
      array_pop($parts);
    }
    $scanner     = new Scanner($parts);
    $completions = Completions::find($scanner, $this);
    fprintf(STDERR, "completions: %s\n", implode(', ', $completions));
    echo implode(PHP_EOL, $completions);
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
      $grammar->add_callback([ $this, 'complete_callback' ]);
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
    $flags      = $this->flags_grammar->parse($scanner);
    $subcommand = $this->parse_subcommand($scanner);

    if ($scanner->not_empty()) {
      Scanner::fatal_error('extra argument: `%s`', $scanner->advance());
    }

    return new ProgramResult($this, $flags, $subcommand);
  }

  function dispatch(\Cthulhu\lib\cli\Lookup $flags) {
    if ($this->callback) {
      call_user_func($this->callback, $flags);
    }
  }
}
