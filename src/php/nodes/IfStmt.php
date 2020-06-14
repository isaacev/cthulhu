<?php

namespace Cthulhu\php\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\php\Builder;

class IfStmt extends Stmt {
  public Expr $test;
  public BlockNode $consequent;
  public $alternate;

  /**
   * @param Expr                  $test
   * @param BlockNode             $consequent
   * @param null|IfStmt|BlockNode $alternate
   * @param Stmt|null             $next
   */
  public function __construct(Expr $test, BlockNode $consequent, $alternate, ?Stmt $next) {
    parent::__construct($next);
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
    return new self($nodes[0], $nodes[1], $nodes[2], $this->next);
  }

  public function from_successor(?EditableSuccessor $successor): IfStmt {
    assert($successor === null || $successor instanceof Stmt);
    return new IfStmt($this->test, $this->consequent, $this->alternate, $successor);
  }

  protected function build_without_preceding_newline(): Builder {
    if ($this->alternate === null) {
      $alternate = (new Builder);
    } else if ($this->alternate instanceof IfStmt) {
      $alternate = (new Builder)
        ->space()
        ->keyword('else')
        ->space()
        ->then($this->alternate->build_without_preceding_newline());
    } else {
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

  public function build(): Builder {
    return (new Builder)
      ->newline_then_indent()
      ->then($this->build_without_preceding_newline())
      ->then($this->next);
  }
}
