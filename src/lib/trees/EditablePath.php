<?php

namespace Cthulhu\lib\trees;

class EditablePath {
  private ?self $parent;
  private ?EditableNodelike $node;

  public function __construct(?self $parent, EditableNodelike $original) {
    $this->parent = $parent;
    $this->node   = $original;
  }

  public function set_node(EditableNodelike $new_node): void {
    $this->node = $new_node;
  }

  public function get_node(): ?EditableNodelike {
    return $this->node;
  }

  public function remove(): void {
    if ($this->node instanceof RemovalHandler) {
      $this->node = $this->node->handle_removal();
    } else {
      $this->node = null;
    }
  }

  public function replace_with(EditableNodelike $replacement): void {
    if ($this->node instanceof ReplacementHandler) {
      $this->node = $this->node->handle_replacement($replacement);
    } else {
      $this->node = $replacement;
    }
  }
}
