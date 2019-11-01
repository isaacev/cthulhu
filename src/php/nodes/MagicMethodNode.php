<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class MagicMethodNode extends Node {
  public $name;
  public $params;
  public $body;

  /**
   * MagicMethodNode constructor.
   * @param string $name
   * @param Variable[] $params
   * @param BlockNode $body
   */
  function __construct(string $name, array $params, BlockNode $body) {
    parent::__construct();
    $this->name = $name;
    $this->params = $params;
    $this->body = $body;
  }

  function to_children(): array {
    return array_merge(
      $this->params,
      [ $this->body ]
    );
  }

  function from_children(array $nodes): Node {
    return new self($this->name, array_slice($nodes, 0, -1), $nodes[-1]);
  }

  function build(): Builder {
    return (new Builder)
      ->keyword('function')
      ->space()
      ->keyword($this->name)
      ->paren_left()
      ->each($this->params, (new Builder)
        ->comma()
        ->space())
      ->paren_right()
      ->space()
      ->then($this->body);
  }
}
