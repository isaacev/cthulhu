<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class FuncExpr extends Expr {
  public array $params;
  public array $used;
  public BlockNode $body;

  /**
   * @param Variable[] $params
   * @param Variable[] $used
   * @param BlockNode  $body
   */
  public function __construct(array $params, array $used, BlockNode $body) {
    parent::__construct();
    $this->params = $params;
    $this->used   = $used;
    $this->body   = $body;
  }

  public function children(): array {
    return array_merge($this->params, $this->used, [ $this->body ]);
  }

  public function from_children(array $children): FuncExpr {
    $params = array_splice($children, 0, count($this->params));
    $used   = array_splice($children, 0, count($this->used));
    $body   = end($children);
    return (new FuncExpr($params, $used, $body))
      ->copy($this);
  }

  public function build(): Builder {
    $used = (new Builder);
    if (!empty($this->used)) {
      $used
        ->space()
        ->keyword('use')
        ->space()
        ->paren_left()
        ->each($this->used, (new Builder)
          ->comma()
          ->space())
        ->paren_right();
    }

    return (new Builder)
      ->keyword('function')
      ->paren_left()
      ->each($this->params, (new Builder)
        ->comma()
        ->space())
      ->paren_right()
      ->then($used)
      ->space()
      ->then($this->body);
  }
}
