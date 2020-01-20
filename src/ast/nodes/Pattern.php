<?php

namespace Cthulhu\ast\nodes;

abstract class Pattern extends Node {
  abstract public function __toString(): string;
}
