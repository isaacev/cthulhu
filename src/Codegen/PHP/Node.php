<?php

namespace Cthulhu\Codegen\PHP;

use Cthulhu\Codegen\Writer;

abstract class Node implements \JsonSerializable {
  public abstract function write(Writer $writer): Writer;
}
