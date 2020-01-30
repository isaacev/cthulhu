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

  public static function call_non_func(Spanlike $call_span, Spanlike $arg_span, Type $call_type, Type $arg_type): Error {
    return (new Error('call to non-function'))
      ->paragraph("A value of type `$call_type` was called as a function:")
      ->snippet($call_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("An argument with the type `$arg_type` was passed:")
      ->snippet($arg_span);
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

  public static function wrong_elem_type(Spanlike $elem_span, int $pos, Type $elem_type, Type $elements_type): Error {
    return (new Error('mismatched list types'))
      ->paragraph("The previous list elements had the type `$elements_type` but element $pos has the type `$elem_type`:")
      ->snippet($elem_span);
  }

  public static function wrong_ctor_args(Spanlike $ctor_span, Type $ctor_type, Type $args_type): Error {
    return (new Error('wrong constructor arguments'))
      ->paragraph("The constructor expected arguments of the form:")
      ->example("$ctor_type")
      ->paragraph("But arguments were provided in the form:")
      ->example("$args_type")
      ->snippet($ctor_span);
  }

  public static function wrong_let_type(Spanlike $note_span, Type $note_type, Spanlike $expr_span, Type $expr_type): Error {
    return (new Error('annotation disagreement'))
      ->paragraph("The let-statement was marked as having the type:")
      ->example("$note_type")
      ->snippet($note_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("But the expression had the type:")
      ->example("$expr_type")
      ->snippet($expr_span);
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
