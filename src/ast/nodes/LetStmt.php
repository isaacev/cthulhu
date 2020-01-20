<?php

namespace Cthulhu\ast\nodes;

class LetStmt extends Stmt {
  public LowerName $name;
  public ?Note $note;
  public Expr $expr;

  /**
   * @param LowerName $name
   * @param Note|null $note
   * @param Expr      $expr
   */
  public function __construct(LowerName $name, ?Note $note, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->note = $note;
    $this->expr = $expr;
  }

  public function children(): array {
    return [ $this->name, $this->note, $this->expr ];
  }
}
