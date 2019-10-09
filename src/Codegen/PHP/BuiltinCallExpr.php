<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Builder;

class BuiltinCallExpr extends Expr {
  public $name;
  public $args;

  function __construct(string $name, array $args) {
    $this->name = $name;
    $this->args = $args;
  }

  public function to_children(): array {
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

  public function jsonSerialize() {
    return [
      'type' => 'BuiltinCallExpr',
      'name' => $this->name,
      'args' => $this->args,
    ];
  }
}
