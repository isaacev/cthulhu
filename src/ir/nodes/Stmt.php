<?php

namespace Cthulhu\ir\nodes;

use Countable;
use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\lib\trees\Nodelike;
use Cthulhu\lib\trees\RemovalHandler;
use Cthulhu\lib\trees\ReplacementHandler;

abstract class Stmt extends Node implements RemovalHandler, ReplacementHandler, EditableSuccessor, Countable {
  public ?Stmt $next;

  public function __construct(?Stmt $next) {
    parent::__construct();
    $this->next = $next;
  }

  public function successor(): ?Stmt {
    return $this->next;
  }

  public function count(): int {
    return 1 + ($this->next ? count($this->next) : 0);
  }

  public function set_next(?Stmt $new_next): void {
    $this->next = $new_next;
  }

  public function last_stmt(): Stmt {
    if ($this->next) {
      return $this->next->last_stmt();
    }
    return $this;
  }

  public function mutable_append(?Stmt $next): void {
    $this->last_stmt()->set_next($next);
  }

  public function handle_removal(): ?Nodelike {
    return $this->next;
  }

  public function handle_replacement(Nodelike $replacement): Nodelike {
    assert($replacement instanceof Stmt);
    $replacement->mutable_append($this->next);
    return $replacement;
  }
}
