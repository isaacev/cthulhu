<?php

namespace Cthulhu\ir\types;

use Cthulhu\Errors\Error;
use Cthulhu\Source;
use Cthulhu\lib\fmt\Foreground;

class Errors {
  public static function wrong_return_type(Source\Span $span, Type $wanted, Type $found): Error {
    return (new Error('incorrect return type'))
      ->paragraph(
        "Expected the function to return the type `$wanted`.",
        "Instead the function body returns the type `$found`:"
      )
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
}
