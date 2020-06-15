<?php

namespace Cthulhu\ast\nodes;

class RecordNote extends Note {
  public array $fields;

  /**
   * @param ParamNode[] $fields
   */
  public function __construct(array $fields) {
    parent::__construct();
    $this->fields = $fields;
  }

  public function children(): array {
    return $this->fields;
  }
}
