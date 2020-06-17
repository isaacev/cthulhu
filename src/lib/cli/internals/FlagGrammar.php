<?php

namespace Cthulhu\lib\cli\internals;

abstract class FlagGrammar implements Describeable {
  protected string $id;
  protected ?string $short;
  protected string $description;

  public function __construct(string $id, ?string $short, string $description) {
    $this->id          = $id;
    $this->short       = $short;
    $this->description = $description;
  }

  public function has_short_form(): bool {
    return $this->short !== null;
  }

  /**
   * @return string[]
   */
  abstract public function completions(): array;

  abstract public function matches(string $token): bool;

  abstract public function parse(string $token, Scanner $scanner): FlagResult;

  public function full_name(): string {
    if ($this->has_short_form()) {
      return "-$this->short, --$this->id";
    } else {
      return "    --$this->id";
    }
  }

  public function description(): string {
    return $this->description;
  }
}
