<?php

namespace Cthulhu\ast\nodes;

class ShallowEnumItem extends ShallowItem {
  public UpperName $name;
  public array $params;
  public array $forms;

  /**
   * @param UpperName       $name
   * @param TypeParamNote[] $params
   * @param FormDecl[]      $forms
   */
  public function __construct(UpperName $name, array $params, array $forms) {
    parent::__construct();
    $this->name   = $name;
    $this->params = $params;
    $this->forms  = $forms;
  }

  public function children(): array {
    return array_merge([ $this->name ], $this->params, $this->forms);
  }
}
