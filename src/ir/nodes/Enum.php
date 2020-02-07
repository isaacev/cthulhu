<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\lib\trees\EditableSuccessor;

class Enum extends Stmt {
  public Name $name;
  public array $forms;

  /**
   * @param Name      $name
   * @param Form[]    $forms
   * @param Stmt|null $next
   */
  public function __construct(Name $name, array $forms, ?Stmt $next) {
    parent::__construct($next);
    $this->name  = $name;
    $this->forms = $forms;
  }

  public function children(): array {
    return array_merge([ $this->name ], $this->forms);
  }

  public function from_children(array $children): Enum {
    return (new Enum($children[0], array_slice($children, 1), $this->next))
      ->copy($this);
  }

  public function from_successor(?EditableSuccessor $successor): Enum {
    assert($successor === null || $successor instanceof Stmt);
    return (new Enum($this->name, $this->forms, $successor))
      ->copy($this);
  }

  public function build(): Builder {
    return (new Builder)
      ->newline()
      ->indent()
      ->paren_left()
      ->keyword('enum')
      ->space()
      ->then($this->name)
      ->increase_indentation()
      ->newline()
      ->indent()
      ->each($this->forms, (new Builder)
        ->newline()
        ->indent())
      ->decrease_indentation()
      ->paren_right();
  }
}
