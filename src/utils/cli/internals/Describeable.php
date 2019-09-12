<?php

namespace Cthulhu\utils\cli\internals;

interface Describeable {
  public function full_name(): string;
  public function description(): string;
}
