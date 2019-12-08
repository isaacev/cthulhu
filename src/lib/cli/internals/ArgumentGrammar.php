<?php

namespace Cthulhu\lib\cli\internals;

abstract class ArgumentGrammar implements Describeable {
  public string $id;
  public string $description;

  function __construct(string $id, string $description) {
    $this->id          = $id;
    $this->description = $description;
  }

  abstract function parse(Scanner $scanner): ArgumentResult;

  abstract function full_name(): string;

  function description(): string {
    return $this->description;
  }

  function __toString(): string {
    return $this->full_name();
  }
}
