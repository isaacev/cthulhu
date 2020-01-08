<?php

namespace Cthulhu\php\nodes;

class Precedence {
  public const LOWEST              = PHP_INT_MIN;
  public const BOOL_KEYWORD_NOT    = 10;
  public const BOOL_KEYWORD_XOR    = 20;
  public const BOOL_KEYWORD_AND    = 30;
  public const EMPTY_YIELD         = 40;
  public const YIELD_FROM          = 50;
  public const ASSIGNMENT          = 60;
  public const TERNARY             = 70;
  public const NULL_COALESCING     = 80;
  public const BOOLEAN_SYMBOL_OR   = 90;
  public const BOOLEAN_SYMBOL_AND  = 100;
  public const BITWISE_OR          = 110;
  public const BITWISE_XOR         = 120;
  public const BITWISE_AND         = 130;
  public const EQUALITY_COMPARISON = 140;
  public const ORDERED_COMPARISON = 150;
  public const BITWISE_SHIFT = 160;
  public const SUM = 170;
  public const STRING_CONCAT = 170;
  public const PRODUCT = 180;
  public const UNARY_NOT = 190;
  public const INSTANCE_OF = 200;
  public const CAST = 210;
  public const BITWISE_NOT = 220;
  public const ERROR_SUPPRESSION = 230;
  public const INCREMENT_DECREMENT = 240;
  public const EXPONENT = 250;
  public const CLONE_AND_NEW = 260;
  public const ARGUMENT_UNPACK = 270;
  public const HIGHEST = PHP_INT_MAX;
}
