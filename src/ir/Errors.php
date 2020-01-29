<?php

namespace Cthulhu\ir;

use Cthulhu\ast\nodes\Operator;
use Cthulhu\err\Error;
use Cthulhu\ir\types\Type;
use Cthulhu\lib\fmt\Foreground;
use Cthulhu\loc\Spanlike;

class Errors {
  public static function wrong_ret_type(Spanlike $ret_span, Spanlike $sig_span, Type $ret_type, Type $sig_type): Error {
    return (new Error('incorrect return type'))
      ->paragraph("The function is defined as returning the type `$sig_type`:")
      ->snippet($sig_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("But the function body returns the type `$ret_type` instead:")
      ->snippet($ret_span);
  }

  public static function call_non_func(Spanlike $call_span, Type $call_type): Error {
    return (new Error('call to non-function'))
      ->paragraph("A value of type `$call_type` was called as a function:")
      ->snippet($call_span);
  }

  public static function wrong_arg_type(Spanlike $arg_span, Type $arg_type, Type $sig_type): Error {
    return (new Error('incorrect argument type'))
      ->paragraph(
        "The function expected an argument of the type `$sig_type`.",
        "But the function call provided an argument of the type `$arg_type`:"
      )
      ->snippet($arg_span);
  }

  public static function wrong_lhs_type(Operator $oper, Spanlike $arg_span, Type $arg_type, Type $sig_type): Error {
    return (new Error('incorrect operand type'))
      ->paragraph(
        "The `$oper` operator expected the left-hand operand to have the type `$sig_type`.",
        "But the expression provided a left-hand operand of the type `$arg_type`:"
      )
      ->snippet($arg_span);
  }

  public static function wrong_rhs_type(Operator $oper, Spanlike $arg_span, Type $arg_type, Type $sig_type): Error {
    return (new Error('incorrect operand type'))
      ->paragraph(
        "The `$oper` operator expected the right-hand operand to have the type `$sig_type`.",
        "But the expression provided a right-hand operand of the type `$arg_type`:"
      )
      ->snippet($arg_span);
  }

  public static function wrong_unary_type(Operator $oper, Spanlike $arg_span, Type $arg_type, Type $sig_type): Error {
    return (new Error('incorrect operand type'))
      ->paragraph(
        "The `$oper` operator expected the operand to have the type `$sig_type`.",
        "But the expression provided an operand of the type `$arg_type`:"
      )
      ->snippet($arg_span);
  }

  public static function no_main_func(): Error {
    return (new Error('no main function'))
      ->paragraph(
        "Without a main function the program won't run.",
        "A main function can be as simple as the following:"
      )
      ->example("#[entry]\nfn main() -> () {\n  -- more code here\n}");
  }
}
