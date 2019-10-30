<?php

namespace Cthulhu\ir;

class Table {
  protected $table = [];

  public function set(HasId $node, $value): HasId {
    $this->table[$node->get_id()] = $value;
    return $node;
  }

  public function has(HasId $node): bool {
    return array_key_exists($node->get_id(), $this->table);
  }

  public function get(HasId $node) {
    return $this->has($node)
      ? $this->table[$node->get_id()]
      : null;
  }

  public function get_by_id(int $id) {
    return array_key_exists($id, $this->table)
      ? $this->table[$id]
      : null;
  }
}
