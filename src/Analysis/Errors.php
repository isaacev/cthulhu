<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\Debug\Foreground;
use Cthulhu\Debug\Report;
use Cthulhu\Errors\Error;
use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Source;
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
    Type $wanted_type
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
        "But, the function body returns the type `$found_type` on line $found_line:"
      )
      ->snippet($found_span, "should return `$wanted_type` instead of `$found_type`");
  }
}
