<?php

namespace Cthulhu\ir\types\hm;

class Record extends TypeOper {
  public array $fields;

  /**
   * @param Type[] $fields
   */
  public function __construct(array $fields) {
    parent::__construct('Record', array_values($fields));
    $this->fields = $fields;
  }

  public function fresh(callable $fresh_rec): Type {
    $new_fields = [];
    foreach ($this->fields as $name => $note) {
      $new_fields[$name] = $fresh_rec($note);
    }
    return new self($new_fields);
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
