<?php

namespace Cthulhu\ir\nodes2;

use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\types\hm\Type;
use Cthulhu\lib\trees\EditableNodelike;

class Name extends Node {
  public Type $type;
  public string $text;
  public Symbol $symbol;

  public function __construct(Type $type, string $text, Symbol $symbol) {
    parent::__construct();
    $this->type   = $type;
    $this->text   = $text;
    $this->symbol = $symbol;
  }

  public function children(): array {
    return [];
  }

  public function from_children(array $children): EditableNodelike {
    return $this;
  }

  public function build(): Builder {
    return (new Builder)
      ->name($this->text, $this->symbol)
      ->colon()
      ->type($this->type);
  }

  public function __toString(): string {
    return $this->text;
  }
}
