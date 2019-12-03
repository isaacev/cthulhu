<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FuncHead extends Node {
  public Name $name;
  public array $params;

  /**
   * @param Name       $name
   * @param Variable[] $params
   */
  function __construct(Name $name, array $params) {
    parent::__construct();
    $this->name   = $name;
    $this->params = $params;
  }

  public function to_children(): array {
    return array_merge(
      [ $this->name ],
      $this->params
    );
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], array_slice($nodes, 1));
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('function')
      ->space()
      ->then($this->name)
      ->paren_left()
      ->each($this->params, (new Builder)
        ->comma()
        ->space())
      ->paren_right();
  }
}
