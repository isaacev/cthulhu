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

  public static function type_does_not_allow_params(Spanlike $note, Type $type): Error {
    return (new Error('type is not parameterized'))
      ->paragraph("Type parameters were provided on a type that doesn't have any parameters:")
      ->example("$type")
      ->snippet($note);
  }

  public static function wrong_number_of_type_params(Spanlike $note, Type $type, int $wanted, int $given): Error {
    return (new Error('wrong number of type parameters'))
      ->paragraph(
        "The wrong number of type parameters were provided.",
        "The type expected $wanted but was given $given:"
      )
      ->example("$type")
      ->snippet($note);
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

  public static function wrong_cond_type(Spanlike $cond_span, Type $cond_type): Error {
    return (new Error('non-boolean condition'))
      ->paragraph(
        "If-conditions must return the `Bool` type.",
        "Instead the condition returned the type:"
      )
      ->example("$cond_type")
      ->snippet($cond_span);
  }

  public static function cons_alt_mismatch(Spanlike $cons_span, Type $cons_type, Spanlike $alt_span, Type $alt_type): Error {
    return (new Error('branch disagreement'))
      ->paragraph(
        "Both branches of an if-expression must return the same type.",
        "The first branch returned the type:"
      )
      ->example("$cons_type")
      ->snippet($cons_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("But the second branch returned the type:")
      ->example("$alt_type")
      ->snippet($alt_span);
  }

  public static function cons_non_unit(Spanlike $cons_span, Type $cons_type): Error {
    return (new Error('branch disagreement'))
      ->paragraph(
        "When an if-expression has only one branch, that branch must return the `()` type.",
        "Instead the first branch returned the type:"
      )
      ->example("$cons_type")
      ->snippet($cons_span);
  }

  public static function wrong_pattern_for_type(Spanlike $spanlike, Type $pattern_type, Type $discriminant_type): Error {
    return (new Error('incompatible pattern'))
      ->paragraph("The match expression was given a value with the type:")
      ->example("$discriminant_type")
      ->paragraph("But the pattern only matches values with the type:")
      ->example("$pattern_type")
      ->snippet($spanlike);
  }

  public static function wrong_arm_type(Spanlike $spanlike, Type $prior_type, Type $wrong_type): Error {
    return (new Error('incompatible branch types'))
      ->paragraph("Previous branches of the match expression returned the type:")
      ->example("$prior_type")
      ->paragraph("But this branch returned the type:")
      ->example("$wrong_type")
      ->snippet($spanlike);
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
