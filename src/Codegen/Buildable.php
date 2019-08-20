<?php

namespace Cthulhu\Codegen;

interface Buildable {
  public function build(): Builder;
}
