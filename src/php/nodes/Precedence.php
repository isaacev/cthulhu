<?php

namespace Cthulhu\php\nodes;

class Precedence {
  const LOWEST              = PHP_INT_MIN;
  const BOOL_KEYWORD_NOT    = 10;
  const BOOL_KEYWORD_XOR    = 20;
  const BOOL_KEYWORD_AND    = 30;
  const EMPTY_YIELD         = 40;
  const YIELD_FROM          = 50;
  const ASSIGNMENT          = 60;
  const TERNARY             = 70;
  const NULL_COALESCING     = 80;
  const BOOLEAN_SYMBOL_OR   = 90;
  const BOOLEAN_SYMBOL_AND  = 100;
  const BITWISE_OR          = 110;
  const BITWISE_XOR         = 120;
  const BITWISE_AND         = 130;
  const EQUALITY_COMPARISON = 140;
  const ORDERED_COMPARISON  = 150;
  const BITWISE_SHIFT       = 160;
  const SUM                 = 170;
  const STRING_CONCAT       = 170;
  const PRODUCT             = 180;
  const UNARY_NOT           = 190;
  const INSTANCE_OF         = 200;
  const CAST                = 210;
  const BITWISE_NOT         = 220;
  const ERROR_SUPPRESSION   = 230;
  const INCREMENT_DECREMENT = 240;
  const EXPONENT            = 250;
  const CLONE_AND_NEW       = 260;
  const ARGUMENT_UNPACK     = 270;
  const HIGHEST             = PHP_INT_MAX;
}
