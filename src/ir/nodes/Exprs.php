<?php

namespace Cthulhu\ir\nodes;

use Countable;

class Exprs extends Node implements Countable {
  public array $exprs;

  /**
   * @param Expr[] $exprs
   */
  public function __construct(array $exprs) {
    parent::__construct();
    $this->exprs = $exprs;
  }

  public function append(Exprs $rest): Exprs {
    return new Exprs(array_merge($this->exprs, $rest->exprs));
  }

  public function count() {
    return count($this->exprs);
  }

  public function get_expr(int $i): Expr {
    return $this->exprs[$i];
  }

  public function children(): array {
    return $this->exprs;
  }

  public function from_children(array $children): Exprs {
    return (new Exprs($children))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline()
      ->indent()
      ->each($this->exprs, (new Builder)
        ->newline()
        ->indent());
  }
}
