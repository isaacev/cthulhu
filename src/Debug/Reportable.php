<?php

namespace Cthulhu\Debug;

interface Reportable {
  public function print(Teletype $tty): Teletype;
}
