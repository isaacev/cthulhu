<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\hm\Func;
use Cthulhu\lib\trees\EditableSuccessor;

class Def extends Stmt {
  public Func $type;
  public Name $name;
  public Names $params;
  public ?Stmt $body;

  public function __construct(Name $name, Names $params, ?Stmt $body, ?Stmt $next) {
    assert($name->type instanceof Func);
    parent::__construct($next);
    $this->type   = $name->type;
    $this->name   = $name;
    $this->params = $params;
    $this->body   = $body;
  }

  public function children(): array {
    return [ $this->name, $this->params, $this->body ];
  }

  public function from_children(array $children): Def {
    return (new self($children[0], $children[1], $children[2], $this->next))
      ->copy($this);
  }

  public function from_successor(?EditableSuccessor $successor): Def {
    assert($successor === null || $successor instanceof Stmt);
    return (new self($this->name, $this->params, $this->body, $successor))
      ->copy($this);
  }

  public function build(): Builder {
    if ($this->body) {
      $body = (new Builder)
        ->paren_left()
        ->increase_indentation()
        ->then($this->body)
        ->decrease_indentation()
        ->paren_right();
    } else {
      $body = (new Builder)
        ->paren_left()
        ->paren_right();
    }

    return (new Builder)
      ->newline()
      ->indent()
      ->paren_left()
      ->keyword('def')
      ->space()
      ->then($this->name)
      ->space()
      ->then($body)
      ->paren_right()
      ->then($this->next ?? (new Builder));
  }
}
