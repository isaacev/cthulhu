<?php

namespace Cthulhu\ir\types;

use Cthulhu\err\Error;
use Cthulhu\lib\fmt\Foreground;
use Cthulhu\loc\Spanlike;

class Errors {
  public static function wrong_ret_type(Spanlike $last_expr, Spanlike $ret_note, hm\Type $wanted, hm\Type $found): Error {
    return (new Error('incorrect return type'))
      ->paragraph("The function is defined as returning the type `$wanted`:")
      ->snippet($ret_note, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("But the function body returns the type `$found` instead:")
      ->snippet($last_expr);
  }

  public static function list_elem_mismatch(Spanlike $wrong_elem, int $wrong_index, hm\Type $wanted, hm\Type $found): Error {
    return (new Error('mismatched list types'))
      ->paragraph("The previous list elements had the type `$wanted` but element " . ($wrong_index + 1) . " has the type `$found`:")
      ->snippet($wrong_elem);
  }

  public static function wrong_arg_type(Spanlike $wrong_arg, hm\Type $wanted, hm\Type $found): Error {
    return (new Error('wrong argument type'))
      ->paragraph("Expected an argument of the type `$wanted` but found an argument of the type `$found` instead:")
      ->snippet($wrong_arg);
  }

  public static function call_to_non_func(Spanlike $extra_arg, hm\Type $callee, hm\Type $arg): Error {
    return (new Error('call to non-function'))
      ->paragraph("Tried to pass an argument with the type `$arg` to an expression with the type `$callee`:")
      ->snippet($extra_arg);
  }

  public static function type_mismatch(Spanlike $spanlike, hm\Type $left, hm\Type $right): Error {
    return (new Error('type mismatch'))
      ->paragraph("Mismatch between the type `$left` and the type `$right`:")
      ->snippet($spanlike);
  }
}
