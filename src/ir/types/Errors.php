<?php

namespace Cthulhu\ir\types;

use Cthulhu\err\Error;
use Cthulhu\ir\nodes;
use Cthulhu\lib\fmt\Foreground;
use Cthulhu\loc\Spanlike;

class Errors {
  public static function wrong_return_type(Spanlike $spanlike, Type $wanted, Type $found): Error {
    return (new Error('incorrect return type'))
      ->paragraph(
        "Expected the function to return the type `$wanted`.",
        "Instead the function body returns the type `$found`:"
      )
      ->snippet($spanlike);
  }

  public static function let_stmt_needs_type(nodes\LetStmt $stmt, Type $expr_type): Error {
    $assignee_span = $stmt->name->get('span');
    return (new Error('statement needs type annotation'))
      ->paragraph("The expression with the type `$expr_type` needs a type annotation to remove ambiguity.")
      ->snippet($assignee_span);
  }

  public static function incompatible_pattern(Spanlike $spanlike, Type $disc_type): Error {
    return (new Error('incompatible pattern'))
      ->paragraph("Pattern is incompatible with the type `$disc_type`:")
      ->snippet($spanlike);
  }

  public static function match_arm_disagreement(Spanlike $spanlike, Type $arm_type, Type $match_type): Error {
    return (new Error('inconsistent match arm types'))
      ->paragraph("Expected all match arms to return the type `$match_type` but found the type `$arm_type` instead:")
      ->snippet($spanlike);
  }

  public static function if_cond_not_bool(Spanlike $spanlike, Type $found): Error {
    return (new Error('non-boolean condition'))
      ->paragraph("Conditions need to have the type `Bool`, found type `$found` instead:")
      ->snippet($spanlike);
  }

  public static function if_else_branch_disagreement(
    Spanlike $if_span,
    Type $if_type,
    Spanlike $else_span,
    Type $else_type
  ): Error {
    return (new Error('incompatible if and else branches'))
      ->paragraph("Expected `$if_type` because of the return type of the if clause:")
      ->snippet($if_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("Instead the else clause returned the type `$else_type`:")
      ->snippet($else_span);
  }

  public static function if_branch_not_returning_unit(Spanlike $if_span, Type $if_type): Error {
    return (new Error('incompatible if and else branches'))
      ->paragraph(
        "Expected the if clause to return `()` because there is no else clause.",
        "Instead the found the type `$if_type`:"
      )
      ->snippet($if_span);
  }

  public static function constructor_on_non_type(Spanlike $spanlike): Error {
    return (new Error('constructor on a module'))
      ->paragraph("A constructor was called on the name of a module.")
      ->snippet($spanlike);
  }

  public static function constructor_on_non_union_type(Spanlike $spanlike, Type $wrong_type): Error {
    return (new Error('constructor on non-union type'))
      ->paragraph("A constructor was called on a type `$wrong_type` instead of on a union type.")
      ->snippet($spanlike);
  }

  public static function no_variant_with_name(Spanlike $spanlike, NamedType $type, string $variant_name): Error {
    return (new Error('unknown variant'))
      ->paragraph("A constructor named '$variant_name' was used by the type `$type` has no variant with that name.")
      ->snippet($spanlike);
  }

  public static function wrong_constructor_argument(Spanlike $spanlike, string $variant_name, Type $expected, Type $found): Error {
    return (new Error('wrong constructor argument'))
      ->paragraph("The constructor for the `$variant_name` variant expected the type:")
      ->example("$expected")
      ->paragraph("But found the type:")
      ->example("$found")
      ->snippet($spanlike);
  }

  public static function wrong_constructor_arguments(Spanlike $spanlike, string $name, Variant $expected, Variant $found): Error {
    return (new Error('wrong constructor arguments'))
      ->paragraph("The constructor for the `$name` variant expected:")
      ->example(trim($expected))
      ->paragraph("But found:")
      ->example(trim($found))
      ->snippet($spanlike);
  }

  public static function let_note_does_not_match_expr(
    Spanlike $note_span,
    Type $note_type,
    Spanlike $expr_span,
    Type $expr_type
  ): Error {
    return (new Error('type mismatch'))
      ->paragraph("The statement was marked as having the type `$note_type`:")
      ->snippet($note_span, null, [ 'color' => Foreground::BLUE ])
      ->paragraph("But the expression has the type `$expr_type`:")
      ->snippet($expr_span);
  }

  public static function call_to_non_function(Spanlike $spanlike, Type $type): Error {
    return (new Error('call to non-function'))
      ->paragraph("Tried to call an expression with the type `$type`:")
      ->snippet($spanlike);
  }

  public static function call_with_wrong_arg_type(Spanlike $spanlike, Type $wanted, Type $found, int $offset = -1): Error {
    $position = $offset >= 0 ? ' ' . ($offset + 1) : '';
    $err      = (new Error('wrong argument type'))
      ->paragraph("Expected argument$position to have the type `$wanted`, found type `$found` instead:")
      ->snippet($spanlike);

    if ($wanted instanceof FreeType || $wanted instanceof FixedType) {
      $err
        ->paragraph("The type `$wanted` was defined here:")
        ->snippet($wanted->symbol->get('node')->get('span'), null, [
          'color' => Foreground::BLUE,
          'underline' => '~',
        ]);
    }

    if ($found instanceof FreeType || $found instanceof FixedType) {
      $err
        ->paragraph("The type `$found` was defined here:")
        ->snippet($found->symbol->get('node')->get('span'), null, [
          'color' => Foreground::BLUE,
          'underline' => '~',
        ]);
    }

    return $err;
  }

  public static function unsolvable_type_parameter(Spanlike $spanlike, nodes\Name $name, Type $unified, Type $component): Error {
    return (new Error('no solution for type parameter'))
      ->paragraph("Type parameter `'$name->value` was already used with the type `$unified` but then was used with the incompatible type `$component`.")
      ->snippet($spanlike);
  }

  public static function unsupported_binary_operator(Spanlike $spanlike, string $op, Type $lhs, Type $rhs): Error {
    return (new Error('unsupported binary operator'))
      ->paragraph("The type `$lhs` doesn't support the binary operator `$op` with the type `$rhs`:")
      ->snippet($spanlike);
  }

  public static function unsupported_unary_operator(Spanlike $spanlike, string $op, Type $rhs): Error {
    return (new Error('unsupported unary operator'))
      ->paragraph("The type `$rhs` doesn't support the unary operator `$op`:")
      ->snippet($spanlike);
  }

  public static function mismatched_list_element_types(Spanlike $spanlike, Type $unified, int $ord, Type $next): Error {
    return (new Error('mismatched list types'))
      ->paragraph("The previous list elements had the type `$unified` but element $ord has the type `$next`:")
      ->snippet($spanlike);
  }

  public static function type_does_not_support_parameters(Spanlike $spanlike, Type $type): Error {
    return (new Error('type does not support type parameters'))
      ->paragraph("The type `$type` does not support type-parameters that refine its behavior:")
      ->snippet($spanlike);
  }

  public static function wrong_num_type_parameters(Spanlike $spanlike, Type $type, int $wanted, int $found): Error {
    return (new Error('wrong number of type parameters'))
      ->paragraph("The type `$type` wanted $wanted type parameters but found $found:")
      ->snippet($spanlike);
  }
}
