<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ArrowExpr extends Expr {
  public array $params;
  public Expr $body;

  /**
   * @param FuncParam[] $params
   * @param Expr        $body
   */
  function __construct(array $params, Expr $body) {
    parent::__construct();
    $this->params = $params;
    $this->body   = $body;
  }

  public function to_children(): array {
    return array_merge([ $this->body ], $this->params);
  }

  public function from_children(array $nodes): Node {
    return new self(array_slice($nodes, 1), $nodes[0]);
  }

  public function precedence(): int {
    return Precedence::LOWEST;
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword('fn')
      ->space()
      ->paren_left()
      ->each($this->params, (new Builder)
        ->comma()
        ->space())
      ->paren_right()
      ->space()
      ->fat_arrow()
      ->space()
      ->expr($this->body);
  }
}
