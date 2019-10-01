<?php

namespace Cthulhu\Codegen;

class Path {
  public $parent;
  public $node;
  protected $has_changed = false;
  protected $replacement = null;

  function __construct(?Path $parent, PHP\Node $node) {
    $this->parent = $parent;
    $this->node = $node;
  }

  function has_changed(): bool {
    return $this->has_changed;
  }

  function rebuild(PHP\Node $new_node): Path {
    return new Path($this->parent, $new_node);
  }

  function replace_with(PHP\Node $replacement): void {
    $this->has_changed = true;
    $this->replacement = $replacement;
  }

  function walk(Table $table): void {
    $table->preorder($this);
    foreach ($this->node->to_children() as $child_node) {
      (new Path($this, $child_node))->walk($table);
    }
    $table->postorder($this);
  }

  function edit(Table $table) {
    $table->preorder($this);
    if ($this->has_changed()) {
      return $this->replacement;
    }

    $new_children = [];
    foreach ($this->node->to_children() as $child_node) {
      if ($this->node instanceof PHP\BlockNode || $this->node instanceof PHP\Program) {
        $new_children = array_merge(
          $new_children,
          (new StmtPath($this, $child_node))->edit($table)
        );
      } else {
        $new_children[] = (new Path($this, $child_node))->edit($table);
      }
    }

    if ($this->has_changed()) {
      return $this->replacement;
    }

    $new_node = $this->node->from_children($new_children);
    $new_path = $this->rebuild($new_node);
    $table->postorder($new_path);
    return $new_path->has_changed()
      ? $new_path->replacement
      : $new_node;
  }
}

class StmtPath extends Path {
  function rebuild(PHP\Node $new_node): Path {
    return new StmtPath($this->parent, $new_node);
  }

  function replace_with(PHP\Node $replacement): void {
    $this->has_changed = true;
    $this->replacement = [$replacement];
  }

  function replace_with_multiple(array $replacements): void {
    $this->has_changed = true;
    $this->replacement = $replacements;
  }

  function remove(): void {
    $this->has_changed = true;
    $this->replacement = [];
  }

  function edit(Table $table) {
    $edited = parent::edit($table);
    if (is_array($edited)) {
      return $edited;
    } else {
      return [ $edited ];
    }
  }
}
