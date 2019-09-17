<?php

namespace Cthulhu\lib\cli\internals;

interface Describeable {
  public function full_name(): string;
  public function description(): string;
}
