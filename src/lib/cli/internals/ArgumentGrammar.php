<?php

namespace Cthulhu\lib\cli\internals;

abstract class ArgumentGrammar implements Describeable {
  public string $id;
  public string $description;

  public function __construct(string $id, string $description) {
    $this->id          = $id;
    $this->description = $description;
  }

  public abstract function parse(Scanner $scanner): ArgumentResult;

  public abstract function full_name(): string;

  public function description(): string {
    return $this->description;
  }

  public function __toString(): string {
    return $this->full_name();
  }
}
