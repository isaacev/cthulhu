<?php

namespace Cthulhu\lib\fmt;

interface Buildable {
  public function build(): Builder;
}
