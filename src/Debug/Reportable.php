<?php

namespace Cthulhu\Debug;

use Cthulhu\utils\fmt\Formatter;

interface Reportable {
  public function print(Formatter $f): Formatter;
}
