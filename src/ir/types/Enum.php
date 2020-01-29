<?php

namespace Cthulhu\ir\types;

class Enum extends ConcreteType {
  public array $forms;

  /**
   * @param string $name
   * @param Type[] $params
   * @param Type[] $forms
   */
  public function __construct(string $name, array $params, array $forms) {
    parent::__construct($name, $params);
    $this->forms = $forms;
  }

  public function fresh(ParameterContext $ctx): Type {
    $new_params = [];
    foreach ($this->params as $param) {
      $new_params[] = $param->fresh($ctx);
    }
    $new_forms = [];
    foreach ($this->forms as $name => $form) {
      $new_forms[$name] = $form->fresh($ctx);
    }
    return new Enum($this->name, $new_params, $new_forms);
  }
}
