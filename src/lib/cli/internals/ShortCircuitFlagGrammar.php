<?php

namespace Cthulhu\lib\cli\internals;

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

  public function __construct(string $id, ?string $short, string $description, callable $callback) {
    parent::__construct($id, $short, $description);
    $this->callback = $callback;
  }

  /**
   * @return string[]
   */
  public function completions(): array {
    if ($this->has_short_form()) {
      return [
        "-$this->short",
        "--$this->id",
      ];
    } else {
      return [ "--$this->id" ];
    }
  }

  public function matches(string $token): bool {
    return (
      $token === $this->id ||
      $token === $this->short
    );
  }

  public function parse(string $token, Scanner $scanner): FlagResult {
    call_user_func($this->callback);
    exit(0);
  }
}
