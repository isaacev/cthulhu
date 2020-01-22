<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class IfStmt extends Stmt {
  public Expr $test;
  public BlockNode $consequent;
  public $alternate;

  /**
   * @param Expr                  $test
   * @param BlockNode             $consequent
   * @param null|IfStmt|BlockNode $alternate
   */
  public function __construct(Expr $test, BlockNode $consequent, $alternate) {
    parent::__construct();
    $this->test       = $test;
    $this->consequent = $consequent;
    $this->alternate  = $alternate;
  }

  public function children(): array {
    return [
      $this->test,
      $this->consequent,
      $this->alternate,
    ];
  }

  public function from_children(array $nodes): Node {
    return new self($nodes[0], $nodes[1], $nodes[2]);
  }

  public function build(): Builder {
    $alternate = (new Builder);
    if ($this->alternate !== null) {
      $alternate = (new Builder)
        ->space()
        ->keyword('else')
        ->space()
        ->then($this->alternate);
    }

    return (new Builder)
      ->keyword('if')
      ->space()
      ->paren_left()
      ->expr($this->test)
      ->paren_right()
      ->space()
      ->then($this->consequent)
      ->then($alternate);
  }
}
