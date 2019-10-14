<?php

namespace Cthulhu\ir\names;

abstract class Symbol implements \Cthulhu\ir\HasId {
  use \Cthulhu\ir\GenerateId;

  abstract function __toString(): string;
}
