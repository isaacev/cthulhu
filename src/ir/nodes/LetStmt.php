<?php

namespace Cthulhu\ir\nodes;

class LetStmt extends Stmt {
  public Name $name;
  public ?Note $note;
  public Expr $expr;

  function __construct(Name $name, ?Note $note, Expr $expr) {
    parent::__construct();
    $this->name = $name;
    $this->note = $note;
    $this->expr = $expr;
  }

  function children(): array {
    return [
      $this->name,
      $this->note,
      $this->expr,
    ];
  }
}
