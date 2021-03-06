<?php

namespace Cthulhu\ir;

use Cthulhu\ast\nodes\FormPattern;
use Cthulhu\ast\nodes\LowerName;
use Cthulhu\ast\nodes\Operator;
use Cthulhu\err\Error;
use Cthulhu\ir\types\Atomic;
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

  public static function access_non_record(Spanlike $access_span, Spanlike $root_span, Type $root_type): Error {
    return (new Error('access field of non-record'))
      ->paragraph("Fields do not exist for non-record values:")
      ->snippet($access_span)
      ->paragraph("Instead of a record, the value had the type:")
      ->example("$root_type")
      ->snippet($root_span, null, [ 'color' => Foreground::BLUE ]);
  }

  public static function access_unknown_field(LowerName $field, Type $root_type): Error {
    return (new Error('unknown field'))
      ->paragraph("Record does not have a field named '$field':")
      ->snippet($field->get('span'))
      ->paragraph("The record has this type:")
      ->example("$root_type");
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
      ->paragraph("The `$oper` operator expected the left-hand operand to have the type:")
      ->example("$sig_type")
      ->paragraph("But the expression provided a left-hand operand with the type:")
      ->example("$arg_type")
      ->snippet($arg_span);
  }

  public static function wrong_rhs_type(Operator $oper, Spanlike $arg_span, Type $arg_type, Type $sig_type): Error {
    return (new Error('incorrect operand type'))
      ->paragraph("The `$oper` operator expected the right-hand operand to have the type:")
      ->example("$sig_type")
      ->paragraph("But the expression provided a right-hand operand with the type:")
      ->example("$arg_type")
      ->snippet($arg_span);
  }

  public static function wrong_unary_type(Operator $oper, Spanlike $arg_span, Type $arg_type, Type $sig_type): Error {
    return (new Error('incorrect operand type'))
      ->paragraph("The `$oper` operator expected the operand to have the type:")
      ->example("$sig_type")
      ->paragraph("But the expression provided an operand with the type:")
      ->example("$arg_type")
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

  /**
   * @param Spanlike           $spanlike
   * @param patterns\Pattern[] $patterns
   * @return Error
   */
  public static function uncovered_patterns(Spanlike $spanlike, array $patterns): Error {
    $n   = count($patterns);
    $err = (new Error('uncovered patterns'))
      ->paragraph('The match expression does not handle all possible patterns.')
      ->snippet($spanlike)
      ->paragraph("The following " . ($n === 1 ? "pattern was" : "$n patterns were") . " not handled:");

    foreach ($patterns as $pattern) {
      $err->example("$pattern");
    }

    return $err;
  }

  public static function redundant_pattern(Spanlike $spanlike, patterns\Pattern $pattern): Error {
    return (new Error('redundant pattern'))
      ->paragraph("The match expression included a pattern that will never be matched.")
      ->example("$pattern")
      ->snippet($spanlike);
  }

  public static function wrong_pattern_for_type(Spanlike $spanlike, Type $pattern_type, Type $discriminant_type): Error {
    return (new Error('incompatible pattern'))
      ->paragraph("The match expression was given a value with the type:")
      ->example("$discriminant_type")
      ->paragraph("But the pattern only matches values with the type:")
      ->example("$pattern_type")
      ->snippet($spanlike);
  }

  public static function wrong_fields_for_form(Spanlike $spanlike, FormPattern $found, Type $expected): Error {
    return (new Error('pattern incompatible with type'))
      ->snippet($spanlike)
      ->paragraph("The branch needs a pattern that matches:")
      ->example($found->path->tail . (Atomic::is_unit($expected) ? "" : "$expected"));
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
