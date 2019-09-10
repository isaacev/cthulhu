<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\Debug\Foreground;
use Cthulhu\Debug\Report;
use Cthulhu\Errors\Error;
use Cthulhu\IR;
use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Parser\Lexer\Point;
use Cthulhu\Source;
use Cthulhu\Types;
use Cthulhu\Types\Type;

class Errors {
  public static function unknown_named_type(AST\NamedAnnotation $note): Error {
    // TODO
  }

  public static function incorrect_return_type(
    Source\File $file,
    Span $found_span,
    Type $found_type,
    Span $wanted_span,
    Type $wanted_type,
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

  public static function function_returns_nothing(
    Source\File $file,
    Span $block_span,
    Span $wanted_span,
    Type $wanted_type,
    ?IR\Node $last_stmt = null,
    ?Span $last_semi = null
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

    if ($last_stmt && $last_semi && $last_stmt instanceof IR\SemiStmt && $wanted_type->accepts($last_stmt->expr->type())) {
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
    Span $found_span,
    Type $found_type
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
    Span $if_true_span,
    Type $if_true_type,
    Span $if_false_span,
    Type $if_false_type
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
    Span $if_true_span,
    Type $if_true_type,
    Span $if_true_block_span
  ): Error {
    $title = 'if expression missing an else clause';
    $if_true_line = $if_true_span->from->line;
    $if_true_height = $if_true_block_span->to->line - $if_true_block_span->from->line;
    return (new Error($file, $title, $if_true_span))
      ->paragraph(
        "If expressions with the else clause evaluate to the type `Void`.",
        "The if clause returned the type `$if_true_type` on line $if_true_line:"
      )
      ->snippet($if_true_span, null, [
        'lines_above' => $if_true_line - $if_true_block_span->from->line,
        'lines_below' => $if_true_block_span->to->line - $if_true_line,
      ])
      ->paragraph(
        "The type `$if_true_type` is incompatible with `Void`.",
        "Try adding an else clause that returns the type `$if_true_type`:"
      )
      ->snippet($if_true_block_span->to->to_span(), 'consider adding an else block here', [
        'color' => Foreground::BLUE,
        'lines_above' => $if_true_height,
      ]);
  }
}
