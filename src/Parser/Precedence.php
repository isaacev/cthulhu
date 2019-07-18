<?php

namespace Cthulhu\Parser;

abstract class Precedence {
  const LOWEST  = 0;
  const SUM     = 10;
  const PRODUCT = 20;
}
