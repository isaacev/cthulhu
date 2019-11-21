<?php

namespace Cthulhu\ir\types;

use Cthulhu\Errors\Error;
use Cthulhu\Source;
use Cthulhu\lib\fmt\Foreground;
use Cthulhu\ir\nodes;

class Errors {
  public static function wrong_return_type(Source\Span $span, Type $wanted, Type $found): Error {
    return (new Error('incorrect return type'))
      ->paragraph(
        "Expected the function to return the type `$wanted`.",
        "Instead the function body returns the type `$found`:"
      )
      ->snippet($span);
  }

  public static function incompatible_pattern(Source\Span $span, Type $disc_type): Error {
    return (new Error('incompatible pattern'))
      ->paragraph("Pattern is incompatible with the type `$disc_type`:")
      ->snippet($span);
  }

  public static function match_arm_disagreement(Source\Span $span, Type $arm_type, Type $match_type): Error {
    return (new Error('inconsistent match arm types'))
      ->paragraph("Expected all match arms to return the type `$match_type` but found the type `$arm_type` instead:")
      ->snippet($span);
  }

  public static function if_cond_not_bool(Source\Span $span, Type $found): Error {
    return (new Error('non-boolean condition'))
      ->paragraph("Conditions need to have the type `Bool`, found type `$found` instead:")
      ->snippet($span);
  }

  public static function if_else_branch_disagreement(
    Source\Span $if_span,
    Type $if_type,
    Source\Span $else_span,
    Type $else_type
  ): Error {
    return (new Error('incompatible if and else branches'))
      ->paragraph("Expected `$if_type` because of the return type of the if clause:")
      ->snippet($if_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("Instead the else clause returned the type `$else_type`:")
      ->snippet($else_span);
  }

  public static function if_branch_not_returning_unit(Source\Span $if_span, Type $if_type): Error {
    return (new Error('incompatible if and else branches'))
      ->paragraph(
        "Expected the if clause to return `()` because there is no else clause.",
        "Instead the found the type `$if_type`:"
      )
      ->snippet($if_span);
  }

  public static function no_variant_with_name(Source\Span $span, UnionType $union, string $variant_name): Error {
    return (new Error('unknown variant'))
      ->paragraph("A constructor named '$variant_name' was used by the type `$union->name` has no variant with that name.")
      ->snippet($span);
  }

  public static function wrong_constructor_arguments(Source\Span $span, string $name, VariantFields $expected, ConstructorFields $found): Error {
    return (new Error('wrong constructor arguments'))
      ->paragraph("The constructor for the `$name` variant expected:")
      ->example(trim($expected))
      ->paragraph("But found:")
      ->example(trim($found))
      ->snippet($span);
  }

  public static function let_note_does_not_match_expr(
    Source\Span $note_span,
    Type $note_type,
    Source\Span $expr_span,
    Type $expr_type
  ): Error {
    return (new Error('type mismatch'))
      ->paragraph("The statement was marked as having the type `$note_type`:")
      ->snippet($note_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("But the expression has the type `$expr_type`:")
      ->snippet($expr_span);
  }

  public static function call_to_non_function(Source\Span $span, Type $type): Error {
    return (new Error('call to non-function'))
      ->paragraph("Tried to call an expression with the type `$type`:")
      ->snippet($span);
  }

  public static function call_with_wrong_arg_num(Source\Span $span, int $wanted, int $found): Error {
    return (new Error('call with the wrong number of arguments'))
      ->paragraph("Expected $wanted total arguments, found $found instead:")
      ->snippet($span);
  }

  public static function call_with_wrong_arg_type(Source\Span $span, int $offset, Type $wanted, Type $found): Error {
    $ordinal = $offset + 1;
    return (new Error('wrong argument type'))
      ->paragraph("Expected argument $ordinal to have the type `$wanted`, found type `$found` instead:")
      ->snippet($span);
  }

  public static function unsolvable_type_parameter(Source\Span $span, nodes\Name $name, Type $unified, Type $component): Error {
    return (new Error('no solution for type parameter'))
      ->paragraph("Type parameter `'$name->value` was already used with the type `$unified` but then was used with the incompatible type `$component`.")
      ->snippet($span);
  }

  public static function unsupported_binary_operator(Source\Span $span, string $op, Type $lhs, Type $rhs): Error {
    return (new Error('unsupported binary operator'))
      ->paragraph("The type `$lhs` doesn't support the binary operator `$op` with the type `$rhs`:")
      ->snippet($span);
  }

  public static function unsupported_unary_operator(Source\Span $span, string $op, Type $rhs): Error {
    return (new Error('unsupported unary operator'))
      ->paragraph("The type `$rhs` doesn't support the unary operator `$op`:")
      ->snippet($span);
  }

  public static function mismatched_list_element_types(Source\Span $span, Type $unified, int $ord, Type $next): Error {
    return (new Error('mismatched list types'))
      ->paragraph("The previous list elements had the type `$unified` but element $ord has the type `$next`:")
      ->snippet($span);
  }
}
