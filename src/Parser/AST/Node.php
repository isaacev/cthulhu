<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

abstract class Node implements \JsonSerializable {
  public abstract function from(): Point;
}
