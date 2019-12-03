<?php

namespace Cthulhu\ir\nodes;

class ParameterizedNote extends Note {
  public Note $inner;
  public array $params;

  /**
   * @param Note   $inner
   * @param Note[] $params
   */
  function __construct(Note $inner, array $params) {
    parent::__construct();
    $this->inner  = $inner;
    $this->params = $params;
  }

  function children(): array {
    return array_merge(
      [ $this->inner ],
      $this->params
    );
  }
}
