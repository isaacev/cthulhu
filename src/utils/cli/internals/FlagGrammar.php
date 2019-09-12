<?php

namespace Cthulhu\utils\cli\internals;

abstract class FlagGrammar implements Describeable {
  function __construct(string $id, string $description) {
    $this->id = $id;
    $this->description = $description;
  }

  abstract function matches(string $token): bool;
  abstract function parse(string $token, Scanner $scanner): FlagResult;
  abstract function full_name(): string;

  function description(): string {
    return $this->description;
  }
}
