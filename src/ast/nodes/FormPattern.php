<?php

namespace Cthulhu\ast\nodes;

abstract class FormPattern extends Pattern {
  public PathNode $path;

  public function __construct(PathNode $path) {
    parent::__construct();
    $this->path = $path;
  }

  abstract public function fields_to_string(): string;

  public function __toString(): string {
    $path = $this->path->tail->get('symbol')->__toString();
    return $path . $this->fields_to_string();
  }
}
