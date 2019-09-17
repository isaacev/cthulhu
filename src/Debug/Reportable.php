<?php

namespace Cthulhu\Debug;

use Cthulhu\lib\fmt\Formatter;

interface Reportable {
  public function print(Formatter $f): Formatter;
}
