<?php

namespace Cthulhu\ir\types;

class ListType extends ConcreteType {
  public Type $elements;

  public function __construct(Type $elements) {
    parent::__construct('List', [ $elements ]);
    $this->elements = $elements;
  }

  public function fresh(ParameterContext $ctx): Type {
    return new ListType($this->elements->fresh($ctx));
  }

  public function __toString(): string {
    return "List($this->elements)";
  }
}
