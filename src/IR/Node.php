<?php

namespace Cthulhu\IR;

use Cthulhu\Types\Type;

abstract class Node implements \JsonSerializable {
  public abstract function type(): Type;
}
