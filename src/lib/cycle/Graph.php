<?php

namespace Cthulhu\lib\cycle;

use Cthulhu\ir\HasId;

class Graph {
  protected $root_id;
  protected $lookup = [];
  protected $edges = [];

  function __construct(HasId $root) {
    $this->lookup[$this->root_id = $root->get_id()] = $root;
  }

  public function add_edge(HasId $from, HasId $to): void {
    $this->lookup[$from_id = $from->get_id()] = $from;
    $this->lookup[$to_id = $to->get_id()]     = $to;

    if (array_key_exists($from_id, $this->edges)) {
      if (in_array($to_id, $this->edges[$from_id]) === false) {
        $this->edges[$from_id][] = $to_id;
      }
    } else {
      $this->edges[$from_id] = [ $to_id ];
    }
  }

  protected function lookup_all(array $ids): array {
    $objects = [];
    foreach ($ids as $id) {
      $objects[] = $this->lookup[$id];
    }
    return $objects;
  }

  protected function edges_from(int $from_id): array {
    if (array_key_exists($from_id, $this->edges)) {
      return $this->edges[$from_id];
    } else {
      return [];
    }
  }

  protected function _find_cycle(int $v, array &$path, array &$done): ?array {
    array_push($path, $v);

    foreach ($this->edges_from($v) as $w) {
      if (in_array($w, $path)) {
        $path_ref_index = array_search($w, $path);
        return [ $path_ref_index, $path ];
      } else if (in_array($w, $done) === false) {
        if ($found_cycle = $this->_find_cycle($w, $path, $done)) {
          return $found_cycle;
        }
      }
    }

    array_pop($path);
    array_push($done, $v);
    return null;
  }

  public function get_cycle(): ?array {
    $path = [];
    $done = [];
    if ([ $index, $full_path ] = $this->_find_cycle($this->root_id, $path, $done)) {
      return [
        $index,
        $this->lookup_all($full_path),
      ];
    } else {
      return null;
    }
  }

  protected function _get_order(int $v, array &$stack, array &$done): void {
    array_push($done, $v);

    foreach ($this->edges_from($v) as $w) {
      if (in_array($w, $done) === false) {
        $this->_get_order($w, $stack, $done);
      }
    }

    array_push($stack, $v);
  }

  public function get_order(): array {
    $stack = [];
    $done  = [];
    $this->_get_order($this->root_id, $stack, $done);
    return $this->lookup_all($stack);
  }
}
