<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\lib\fmt\Foreground;
use Cthulhu\Debug\Report;
use Cthulhu\Errors\Error;
use Cthulhu\IR;
use Cthulhu\Source;

class Errors {
  public static function no_entry_point(Source\File $file): Error {
    $title = 'no main function';
    return (new Error($file, $title, (new Source\Point)->to_span()))
      ->paragraph(
        "Without a main function the program won't run.",
        "A main function can be as simple as the following:"
      )
      ->example("fn main() -> () {\n  -- more code here\n}");
  }

  public static function unknown_named_type(Source\File $file, AST\NamedAnnotation $note): Error {
    $title = 'unknown type';
    return (new Error($file, $title, $note->span))
      ->paragraph(
        "No type in scope named `$note->path`."
      )
      ->snippet($note->span);
  }

  public static function incorrect_return_type(
    Source\File $file,
    Source\Span $found_span,
    IR\Types\Type $found_type,
    Source\Span $wanted_span,
    IR\Types\Type $wanted_type,
    ?IR\Node $last_stmt = null
  ): Error {
    $title = 'incorrect return type';
    $wanted_line = $wanted_span->from->line;
    $found_line = $found_span->from->line;
    return (new Error($file, $title, $found_span))
      ->paragraph(
        "Expected the function to return the type `$wanted_type` because of the type signature on line $wanted_line:"
      )
      ->snippet($wanted_span, null, [
        'color' => Foreground::BLUE,
        'underline' => '~'
      ])
      ->paragraph(
        "But the function body returns the type `$found_type` on line $found_line:"
      )
      ->snippet($found_span, "should return `$wanted_type` instead of `$found_type`");
  }

  public static function binding_disagrees_with_expr(
    Source\File $file,
    IR\Types\Type $note_type,
    Source\Span $note_span,
    IR\Types\Type $expr_type,
    Source\Span $expr_span
  ): Error {
    $title = 'expression disagrees with type annotation';
    return (new Error($file, $title, $expr_span))
      ->paragraph("The expression returns the type `$expr_type`:")
      ->snippet($expr_span)
      ->paragraph("But the variable binding expected a type compatible with `$note_type`:")
      ->snippet($note_span, null, [
        'color' => Foreground::BLUE,
        'underline' => '~',
      ]);
  }

  public static function function_returns_nothing(
    Source\File $file,
    Source\Span $block_span,
    Source\Span $wanted_span,
    IR\Types\Type $wanted_type,
    ?IR\Node $last_stmt = null,
    ?Source\Span $last_semi = null
  ): Error {
    $title = 'incorrect return type';
    $wanted_line = $wanted_span->from->line;
    $err = (new Error($file, $title, $block_span->to->to_span()))
      ->paragraph(
        "Expected the function to return the type `$wanted_type` because of the type signature on line $wanted_line:"
      )
      ->snippet($wanted_span, null, [
        'color' => Foreground::BLUE,
        'underline' => '~'
      ])
      ->paragraph(
        "But the function body returns nothing:"
      )
      ->snippet($block_span);

    if ($last_stmt && $last_semi && $last_stmt instanceof IR\SemiStmt && $wanted_type->equals($last_stmt->expr->return_type())) {
      $err
        ->paragraph(
          "However the last statement in the block might return the correct value if it wasn't followed by a semicolon:"
        )
        ->snippet($last_semi, "consider removing this semicolon", [
          'color' => Foreground::BLUE,
        ]);
    }

    return $err;
  }

  public static function condition_not_bool(
    Source\File $file,
    Source\Span $found_span,
    IR\Types\Type $found_type
  ): Error {
    $title = 'non-boolean condition';
    return (new Error($file, $title, $found_span))
      ->paragraph(
        "Conditions need to have the type `Bool`, found type `$found_type` instead:"
      )
      ->snippet($found_span);
  }

  public static function incompatible_if_and_else_types(
    Source\File $file,
    Source\Span $if_true_span,
    IR\Types\Type $if_true_type,
    Source\Span $if_false_span,
    IR\Types\Type $if_false_type
  ): Error {
    $title = 'incompatible if and else types';
    return (new Error($file, $title, $if_false_span))
      ->paragraph(
        "Expected `$if_true_type` because of the return type of the if clause:"
      )
      ->snippet($if_true_span, null, [
        'color' => Foreground::BLUE,
      ])
      ->paragraph(
        "Expected the else clause to return a type compatible with `$if_true_type`.",
        "Instead it returned `$if_false_type`:"
      )
      ->snippet($if_false_span);
  }

  public static function if_block_incompatible_with_void(
    Source\File $file,
    Source\Span $if_true_span,
    IR\Types\Type $if_true_type,
    Source\Span $if_true_block_span
  ): Error {
    $title = 'if expression missing an else clause';
    $if_true_line = $if_true_span->from->line;
    $if_true_height = $if_true_block_span->to->line - $if_true_block_span->from->line;
    return (new Error($file, $title, $if_true_span))
      ->paragraph(
        "If expressions with the else clause evaluate to the type `()`.",
        "The if clause returned the type `$if_true_type` on line $if_true_line:"
      )
      ->snippet($if_true_span, null, [
        'lines_above' => $if_true_line - $if_true_block_span->from->line,
        'lines_below' => $if_true_block_span->to->line - $if_true_line,
      ])
      ->paragraph(
        "The type `$if_true_type` is incompatible with `()`.",
        "Try adding an else clause that returns the type `$if_true_type`:"
      )
      ->snippet($if_true_block_span->to->to_span(), 'consider adding an else block here', [
        'color' => Foreground::BLUE,
        'lines_above' => $if_true_height,
      ]);
  }

  public static function unknown_local_variable(
    Source\File $file,
    Source\Span $span,
    string $name
  ): Error {
    $title = 'unknown variable';
    return (new Error($file, $title, $span))
      ->paragraph("Referenced `$name` before it was declared.")
      ->snippet($span);
  }

  public static function unknown_submodule(
    Source\File $file,
    IR\Symbol $parent_module,
    Source\Span $child_span,
    string $child_name
  ): Error {
    $title = 'unknown module';
    return (new Error($file, $title, $child_span))
      ->paragraph("The `$parent_module` module doesn't have a submodule named `$child_name`")
      ->snippet($child_span);
  }

  public static function unknown_external_module(
    Source\File $file,
    Source\Span $span,
    string $name
  ): Error {
    $title = 'unknown external module';
    return (new Error($file, $title, $span))
      ->paragraph("No known external module named `$name`.")
      ->snippet($span);
  }

  public static function unknown_module_field(
    Source\File $file,
    IR\Symbol $parent_module,
    Source\Span $field_span,
    string $field_name
  ): Error {
    $title = 'unknown module field';
    return (new Error($file, $title, $field_span))
      ->paragraph("The `$parent_module` module doesn't have a field named `$field_name`")
      ->snippet($field_span);
  }

  public static function value_referenced_as_module(
    Source\File $file,
    Source\Span $value_span,
    IR\Symbol $value_symbol,
    IR\Types\Type $value_type,
    Source\Span $submodule_span
  ): Error {
    $title = 'value referenced as module';
    return (new Error($file, $title, $value_span))
      ->paragraph("The value `$value_symbol` has the type `$value_type` but was referenced as a module:")
      ->snippet($submodule_span);
  }

  public static function func_called_with_wrong_num_or_args(
    Source\File $file,
    Source\Span $call_site,
    int $num_args_given,
    IR\Types\FunctionType $callee_type
  ): Error {
    $title = 'wrong number of arguments';
    $num_args_expected = count($callee_type->inputs);
    $s = $num_args_expected === 1 ? '' : 's';
    return (new Error($file, $title, $call_site))
      ->paragraph("The expected $num_args_expected argument${s}, $num_args_given given:")
      ->snippet($call_site);
  }

  public static function unsupported_binary_operator(
    Source\File $file,
    Source\Span $expr_span,
    string $op,
    IR\Types\Type $lhs
  ): Error {
    $title = 'unsupported binary operator';
    return (new Error($file, $title, $expr_span))
      ->paragraph("The type `$lhs` does not support the `$op` operator.")
      ->snippet($expr_span);
  }

  public static function unsupported_unary_operator(
    Source\File $file,
    Source\Span $expr_span,
    string $op,
    IR\Types\Type $operand_type
  ): Error {
    $title = 'unsupported unary operator';
    return (new Error($file, $title, $expr_span))
      ->paragraph("The type `$operand_type` does not support the `$op` operator.")
      ->snippet($expr_span);
  }

  public static function unknown_generic_type(
    Source\File $file,
    Source\Span $span,
    string $name
  ): Error {
    $title = 'unknown type parameter';
    return (new Error($file, $title, $span))
      ->paragraph("No type parameter named `$name` in scope.")
      ->snippet($span);
  }

  public static function type_mismatch(
    Source\File $file,
    IR\Types\Type $found_type,
    Source\Span $found_span,
    IR\Types\Type $wanted_type
  ): Error {
    $title = 'type mismatch';
    return (new Error($file, $title, $found_span))
      ->paragraph("Expected the type `$wanted_type`, found `$found_type` instead.")
      ->snippet($found_span);
  }

  public static function called_a_non_function(
    Source\File $file,
    Source\Span $span,
    IR\Types\Type $type
  ): Error {
    $title = 'called non function';
    return (new Error($file, $title, $span))
      ->paragraph("Tried to call an expression with type `$type`, expected a function instead.")
      ->snippet($span);
  }
}
