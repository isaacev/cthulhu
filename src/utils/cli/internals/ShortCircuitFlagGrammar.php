<?php

namespace Cthulhu\utils\cli\internals;

/**
 * Some flags should stop the parsing and emit as result as soon as detected.
 * These flags are mostly limited to builtin flags like --help and --version.
 * The reason for stopping the parsing immediately is that if the parsing
 * continues, the user might get a message about a missing required argument for
 * the subcommand instead of useful help information.
 *
 * This class is given a callback function to call as soon as the `parse` method
 * is called. The `parse` method will also exit the current process and thus
 * never returns to the parent ProgramGrammar or SubcommandGrammar.
 */
class ShortCircuitFlagGrammar extends FlagGrammar {
  public $callback;

  function __construct(string $id, string $description, callable $callback) {
    parent::__construct($id, $description);
    $this->callback = $callback;
  }

  function completions(): array {
    return ["--$this->id"];
  }

  function matches(string $token): bool {
    return $token === $this->id;
  }

  function parse(string $token, Scanner $scanner): FlagResult {
    call_user_func($this->callback);
    exit(0);
  }

  function full_name(): string {
    return "--$this->id";
  }
}
