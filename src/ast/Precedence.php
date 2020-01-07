<?php

namespace Cthulhu\ast;

class Precedence {
  public const LOWEST   = 0;
  public const RELATION = 10;
  public const PIPE     = 15;
  public const SUM      = 20;
  public const PRODUCT  = 30;
  public const UNARY    = 35;
  public const EXPONENT = 38;
  public const ACCESS   = 40;
}
