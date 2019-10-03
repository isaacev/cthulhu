<?php

namespace Cthulhu\Parser;

abstract class Precedence {
  const LOWEST   = 0;
  const RELATION = 10;
  const SUM      = 20;
  const PRODUCT  = 30;
  const UNARY    = 35;
  const ACCESS   = 40;
}
