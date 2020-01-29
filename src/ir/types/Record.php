<?php

namespace Cthulhu\ir\types;

class Record extends ConcreteType {
  public array $fields;

  /**
   * @param Type[] $fields
   */
  public function __construct(array $fields) {
    parent::__construct('Record', array_values($fields));
    $this->fields = $fields;
  }

  public function fresh(ParameterContext $ctx): Type {
    $new_fields = [];
    foreach ($this->fields as $name => $note) {
      $new_fields[$name] = $note->fresh($ctx);
    }
    return new Record($new_fields);
  }

  public function __toString(): string {
    $out = "";
    foreach ($this->fields as $name => $note) {
      if ($out === "") {
        $out .= " $name: $note";
      } else {
        $out .= ", $name: $note";
      }
    }
    return "{" . $out . " }";
  }
}
