<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\types\Type;

class NamedFormPattern extends FormPattern {
  public array $mapping;

  /**
   * @param Type             $type
   * @param RefSymbol        $ref_symbol
   * @param NamedFormField[] $mapping
   */
  public function __construct(Type $type, RefSymbol $ref_symbol, array $mapping) {
    parent::__construct($type, $ref_symbol);
    $this->mapping = $mapping;
  }

  public function children(): array {
    return array_values($this->mapping);
  }

  public function from_children(array $children): NamedFormPattern {
    $new_mapping = [];
    foreach (array_keys($this->mapping) as $index => $field_name) {
      $new_mapping[$field_name] = $children[$index];
    }
    return new NamedFormPattern($this->type, $this->ref_symbol, $new_mapping);
  }

  public function build(): Builder {
    return (new Builder)
      ->keyword("$this->ref_symbol")
      ->space()
      ->keyword('{')
      ->space()
      ->each(array_values($this->mapping), (new Builder)
        ->keyword(',')
        ->space())
      ->keyword('}');
  }

  public function __toString(): string {
    return "$this->ref_symbol";
  }
}
