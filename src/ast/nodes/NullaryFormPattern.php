<?php

namespace Cthulhu\ast\nodes;

class NullaryFormPattern extends FormPattern {
  public function children(): array {
    return [];
  }

  public function fields_to_string(): string {
    return '';
  }
}
