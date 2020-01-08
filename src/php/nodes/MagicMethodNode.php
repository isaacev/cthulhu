<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class MagicMethodNode extends Node {
  public string $name;
  public array $params;
  public BlockNode $body;

  /**
   * @param string     $name
   * @param Variable[] $params
   * @param BlockNode  $body
   */
  public function __construct(string $name, array $params, BlockNode $body) {
    parent::__construct();
    $this->name   = $name;
    $this->params = $params;
    $this->body   = $body;
  }

  public function to_children(): array {
    return array_merge(
      $this->params,
      [ $this->body ]
    );
  }

  public function from_children(array $nodes): Node {
    return new self($this->name, array_slice($nodes, 0, -1), $nodes[-1]);
  }

  public function build(): Builder {
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
