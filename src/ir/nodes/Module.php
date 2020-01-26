<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableSuccessor;
use Cthulhu\lib\trees\Nodelike;
use Cthulhu\lib\trees\RemovalHandler;
use Cthulhu\lib\trees\ReplacementHandler;

class Module extends Node implements RemovalHandler, ReplacementHandler, EditableSuccessor, \Countable {
  public ?Name $name;
  public ?Stmt $stmt;
  private ?Module $next;

  public function __construct(?Name $name, ?Stmt $stmt, ?Module $next) {
    parent::__construct();
    $this->name = $name;
    $this->stmt = $stmt;
    $this->next = $next;
  }

  public function children(): array {
    return [ $this->name, $this->stmt ];
  }

  public function from_children(array $children): Module {
    return (new self($children[0], $children[1], $this->next))
      ->copy($this);
  }

  public function successor(): ?Module {
    return $this->next;
  }

  public function from_successor(?EditableSuccessor $successor): Module {
    assert($successor === null || $successor instanceof Module);
    return (new self($this->name, $this->stmt, $successor))
      ->copy($this);
  }

  public function count() {
    return 1 + ($this->next ? count($this->next) : 0);
  }

  public function set_next(?Module $new_next): void {
    $this->next = $new_next;
  }

  public function last_module(): Module {
    if ($this->next) {
      return $this->next->last_module();
    }
    return $this;
  }

  public function mutable_append(?Module $next): void {
    $this->last_module()->set_next($next);
  }

  public function handle_removal(): ?Nodelike {
    return $this->next;
  }

  public function handle_replacement(Nodelike $replacement): Nodelike {
    assert($replacement instanceof Module);
    $replacement->mutable_append($this->next);
    return $replacement;
  }

  public function build(): Builder {
    return (new Builder)
      ->newline()
      ->indent()
      ->paren_left()
      ->keyword('mod')
      ->space()
      ->then($this->name ?? (new Builder)->paren_left()->paren_right())
      ->increase_indentation()
      ->then($this->stmt ?? (new Builder))
      ->decrease_indentation()
      ->paren_right()
      ->then($this->next ?? (new Builder));
  }
}
