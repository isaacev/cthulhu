<?php

namespace Cthulhu\php\nodes;

use Cthulhu\php\Builder;

class ClassStmt extends Stmt {
  public $is_abstract;
  public $name;
  public $parent_class;
  public $body;

  /**
   * ClassStmt constructor.
   * @param bool $is_abstract
   * @param Name $name
   * @param Reference|null $parent_class
   * @param MagicMethodNode[] $body
   */
  function __construct(bool $is_abstract, Name $name, ?Reference $parent_class, array $body) {
    parent::__construct();
    $this->is_abstract = $is_abstract;
    $this->name = $name;
    $this->parent_class = $parent_class;
    $this->body = $body;
  }

  function to_children(): array {
    return [
      $this->name,
      $this->parent_class,
    ];
  }

  function from_children(array $nodes): Node {
    return new self($this->is_abstract, $nodes[0], $nodes[1], array_slice($nodes, 2));
  }

  function build(): Builder {
    $parent_class = $this->parent_class === null
      ? (new Builder)
      : (new Builder)
        ->keyword('extends')
        ->space()
        ->reference($this->parent_class)
        ->space();

    $body = empty($this->body)
      ? (new Builder)
      : (new Builder)
        ->increase_indentation()
        ->newline_then_indent()
        ->each($this->body, (new Builder)
          ->newline_then_indent())
        ->decrease_indentation()
        ->newline_then_indent();

    return (new Builder)
      ->maybe($this->is_abstract, (new Builder)
        ->keyword('abstract')
        ->space())
      ->keyword('class')
      ->space()
      ->then($this->name)
      ->space()
      ->then($parent_class)
      ->brace_left()
      ->then($body)
      ->brace_right();
  }
}
