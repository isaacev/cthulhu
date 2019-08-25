<?php

namespace Cthulhu\IR;

use Cthulhu\Types;

interface Module extends \JsonSerializable {
  public function scope(): ModuleScope;
}
