<?php

namespace Cthulhu\ir\types\hm;

use Countable;
use Cthulhu\loc\Spanlike;

class DoExpr extends Expr implements Countable {
  /* @var Expr[] $body */
  public array $body;

  /**
   * @param Spanlike $spanlike
   * @param Expr[]   $body
   */
  public function __construct(Spanlike $spanlike, array $body) {
    parent::__construct($spanlike);
    assert(!empty($body));
    $this->body = $body;
  }

  public function count() {
    return count($this->body);
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
