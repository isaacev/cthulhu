<?php

namespace Cthulhu\ast\nodes;

class NamedFormDecl extends FormDecl {
  public array $params;

  /**
   * @param UpperName   $name
   * @param ParamNode[] $params
   */
  public function __construct(UpperName $name, array $params) {
    parent::__construct($name);
    $this->params = $params;
  }

  public function children(): array {
    return array_merge([ $this->name ], $this->params);
  }
}
