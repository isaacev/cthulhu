<?php

namespace Cthulhu\ast\nodes;

class OrderedFormDecl extends FormDecl {
  public array $params;

  /**
   * @param UpperName $name
   * @param Note[]    $params
   */
  public function __construct(UpperName $name, array $params) {
    parent::__construct($name);
    $this->params = $params;
  }

  public function children(): array {
    return array_merge([ $this->name ], $this->params);
  }
}
