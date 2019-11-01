<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Errors\Error;
use Cthulhu\Source;

class Errors {
  public static function unexpected_character(Character $char): Error {
    return (new Error('unexpected symbol'))
        ->paragraph(
          'File contains a symbol that is not part of the syntax.',
          'Try removing it?'
        )
        ->snippet($char->point->to_span());
  }

  public static function unnamed_type_param(Source\Span $location): Error {
    return (new Error('unnamed type parameter'))
      ->paragraph('Type parameters should look like `\'a`.')
      ->snippet($location);
  }

  public static function unclosed_string(Source\Span $location): Error {
    return (new Error('unclosed string'))
      ->paragraph(
        'String extends to the end of a line without a closing double quote.'
      )
      ->snippet($location);
  }

  public static function invalid_float(Source\Span $span): Error {
    return (new Error('invalid floating point number'))
      ->paragraph('Floating point numbers must have at least 1 whole digit and at least 1 decimal digit.')
      ->snippet($span);
  }
}
