<?php

namespace Cthulhu\ir\nodes;

abstract class Pattern extends Node {
  public abstract function __toString(): string;
}
