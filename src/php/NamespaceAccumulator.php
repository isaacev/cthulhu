<?php

namespace Cthulhu\php;

use Cthulhu\ir\nodes as ir;

class NamespaceAccumulator {
  private Names $names;
  private StatementAccumulator $stmts;

  /* @var nodes\Reference[] $helper_refs */
  private array $helper_refs = [];

  private ?nodes\NamespaceNode $helpers = null;

  /* @var nodes\NamespaceNode[] */
  private array $completed = [];

  /* @var nodes\Reference[] */
  private array $pending_names = [];

  public function __construct(Names $names, StatementAccumulator $stmts) {
    $this->names = $names;
    $this->stmts = $stmts;
  }

  public function current_ref(): nodes\Reference {
    assert(!empty($this->pending_names));
    return end($this->pending_names);
  }

  public function open_anonymous(): void {
    $this->names->enter_namespace_scope();
    $this->stmts->push_block();
  }

  public function close_anonymous(): void {
    $block = $this->stmts->pop_block();
    $this->names->exit_namespace_scope();
    $space = new nodes\NamespaceNode(null, $block);
    array_push($this->completed, $space);
  }

  public function open(ir\Name $name): void {
    $ref = $this->names->name_to_ref($name);
    array_push($this->pending_names, $ref);
    $this->names->enter_namespace_scope();
    $this->stmts->push_block();
  }

  public function close(): void {
    $ref   = array_pop($this->pending_names);
    $block = $this->stmts->pop_block();
    $this->names->exit_namespace_scope();
    $space = new nodes\NamespaceNode($ref, $block);
    array_push($this->completed, $space);
  }

  /**
   * @return nodes\NamespaceNode[]
   */
  public function collect(): array {
    assert(empty($this->pending));
    if ($this->helpers !== null) {
      return [ $this->helpers, ...$this->completed ];
    }
    return $this->completed;
  }

  public function helper(string $name): nodes\Reference {
    if ($this->helpers === null) {
      $this->helpers = new nodes\NamespaceNode(
        new nodes\Reference('runtime', new names\Symbol()),
        new nodes\BlockNode(null),
      );
    }

    if (array_key_exists($name, $this->helper_refs)) {
      return $this->helper_refs[$name];
    } else {
      $stmt     = Helpers::get($name, $this->helpers->name);
      $symbol   = $stmt->head->name->symbol;
      $segments = $this->helpers->name->segments . '\\' . $stmt->head->name->value;
      $ref      = new nodes\Reference($segments, $symbol);

      $this->helpers->block->stmt = $stmt;
      return $this->helper_refs[$name] = $ref;
    }
  }
}
