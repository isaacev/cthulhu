<?php

namespace Cthulhu\IR\Types;

abstract class Type {
  abstract function equals(self $other): bool;
  abstract function __toString(): string;
}
