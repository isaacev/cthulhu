<?php

namespace Cthulhu\ast\nodes;

class ParameterizedNote extends Note {
  public NamedNote $inner;
  public array $params;

  /**
   * @param NamedNote $inner
   * @param Note[]    $params
   */
  public function __construct(NamedNote $inner, array $params) {
    parent::__construct();
    assert(!empty($params));
    $this->inner  = $inner;
    $this->params = $params;
  }

  public function children(): array {
    return array_merge([ $this->inner ], $this->params);
  }
}
