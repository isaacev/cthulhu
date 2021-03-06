<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class BuiltinCallExpr extends Expr {
  public string $name;
  public array $args;

  /**
   * @param string $name
   * @param Expr[] $args
   */
  public function __construct(string $name, array $args) {
    parent::__construct();
    $this->name = $name;
    $this->args = $args;
  }

  public function children(): array {
    return array_merge($this->args);
  }

  public function from_children(array $nodes): Node {
    return new self($this->name, $nodes);
  }

  public function precedence(): int {
    return 40;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword($this->name)
      ->paren_left()
      ->each($this->args, (new Builder)
        ->comma()
        ->space())
      ->paren_right();
  }
}
