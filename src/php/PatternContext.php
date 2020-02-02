<?php

namespace Cthulhu\php;

class PatternContext {
  public nodes\Variable $discriminant;

  /* @var nodes\Expr[] $contextx */
  public array $conditions = [];

  /* @var nodes\Expr[] $contextx */
  public array $accessors = [];

  public function __construct(nodes\Variable $discriminant) {
    $this->discriminant = $discriminant;
  }

  public function push_condition(nodes\Expr $next): void {
    array_push($this->conditions, $next);
  }

  public function peek_condition(): nodes\Expr {
    assert(!empty($this->conditions));
    return end($this->conditions);
  }

  /**
   * @return nodes\Expr[]
   */
  public function pop_conditions(): array {
    return array_splice($this->conditions, 0);
  }

  public function push_accessor(nodes\Expr $next): void {
    array_push($this->accessors, $next);
  }

  public function peek_accessor(): nodes\Expr {
    assert(!empty($this->accessors));
    return end($this->accessors);
  }

  public function pop_accessor(): nodes\Expr {
    assert(!empty($this->accessors));
    return array_pop($this->accessors);
  }
}
