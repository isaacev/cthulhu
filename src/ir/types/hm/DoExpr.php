<?php

namespace Cthulhu\ir\types\hm;

class DoExpr extends Expr {
  /* @var Expr[] $body */
  public array $body;

  /**
   * @param Expr[] $body
   */
  public function __construct(array $body) {
    assert(!empty($body));
    $this->body = $body;
  }

  public function build(): Builder {
    return (new Builder)
      ->paren_left()
      ->keyword('do')
      ->space()
      ->then($this->body[0])
      ->increase_indentation(4)
      ->maybe(count($this->body) > 1, (new Builder)
        ->newline()
        ->indent()
        ->each(array_slice($this->body, 1), (new Builder)
          ->newline()
          ->indent()))
      ->paren_right()
      ->decrease_indentation();
  }
}
