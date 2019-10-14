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

  public static function unclosed_string(Source\Span $location): Error {
    return (new Error('unclosed string'))
      ->paragraph(
        'String extends to the end of a line without a closing double quote.'
      )
      ->snippet($location);
  }
}
