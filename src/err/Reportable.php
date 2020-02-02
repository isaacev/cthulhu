<?php

namespace Cthulhu\err;

use Cthulhu\lib\fmt\Formatter;

interface Reportable {
  public function print(Formatter $f): Formatter;
}
