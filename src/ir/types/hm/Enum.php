<?php

namespace Cthulhu\ir\types\hm;

class Enum extends TypeOper {
  private array $forms;

  /**
   * @param string     $name
   * @param Type[]     $types
   * @param callable[] $forms
   */
  public function __construct(string $name, array $types, array $forms) {
    parent::__construct($name, $types);
    $this->forms = $forms;
  }

  public function get_form(string $name): ?Type {
    if (array_key_exists($name, $this->forms)) {
      return $this->forms[$name]($this->types);
    } else {
      return null;
    }
  }

  public function fresh(callable $fresh_rec): Type {
    $new_types = [];
    foreach ($this->types as $t) {
      $new_types[] = $t->fresh($fresh_rec);
    }
    return new self($this->name, $new_types, $this->forms);
  }
}
