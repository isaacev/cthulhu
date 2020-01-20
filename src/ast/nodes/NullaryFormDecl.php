<?php

namespace Cthulhu\ast\nodes;

class NullaryFormDecl extends FormDecl {
  public function children(): array {
    return [ $this->name ];
  }
}
