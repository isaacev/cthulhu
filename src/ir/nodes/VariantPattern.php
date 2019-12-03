<?php

namespace Cthulhu\ir\nodes;

class VariantPattern extends Pattern {
  public Ref $ref;
  public ?VariantPatternFields $fields;

  function __construct(Ref $ref, ?VariantPatternFields $fields) {
    parent::__construct();
    $this->ref    = $ref;
    $this->fields = $fields;
  }

  public function children(): array {
    return [
      $this->ref,
      $this->fields,
    ];
  }

  public function __toString(): string {
    if ($this->fields) {
      return (string)$this->ref . (string)$this->fields;
    } else {
      return (string)$this->ref;
    }
  }
}
