<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Debug\Report;
use Cthulhu\Errors\Error;
use Cthulhu\Source;

class Errors {
  public static function unexpected_character(Source\File $file, Character $char): Error {
    $title = 'unexpected symbol';
    return (new Error($file, $title, $char->point->to_span()))
        ->paragraph(
          'File contains a symbol that is not part of the syntax.',
          'Try removing it?'
        )
        ->snippet($char->point->to_span());
  }

  public static function unclosed_string(Source\File $file, Source\Span $location): Error {
    $title = 'unclosed string';
    return (new Error($file, $title, $location))
      ->paragraph(
        'String extends to the end of a line without a closing double quote.'
      )
      ->snippet($location);
  }

  public static function unnamed_type_param(Source\File $file, Source\Span $location): Error {
    $title = 'unnamed type parameter';
    return (new Error($file, $title, $location))
      ->snippet($location);
  }
}
