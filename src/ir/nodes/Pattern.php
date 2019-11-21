<?php

namespace Cthulhu\ir\nodes;

abstract class Pattern extends Node {
  abstract function __toString(): string;
}
