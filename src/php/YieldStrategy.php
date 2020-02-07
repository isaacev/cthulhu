<?php

namespace Cthulhu\php;

abstract class YieldStrategy {
  public const SHOULD_RETURN = 'return';
  public const SHOULD_ASSIGN = 'assign';
  public const SHOULD_IGNORE = 'ignore';
}
