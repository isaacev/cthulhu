<?php

namespace Cthulhu\Parser\Lexer;

use Cthulhu\Errors\Error;
use Cthulhu\Debug\Report;

class Errors {
  public static function unexpected_character(string $program, Character $char): Error {
    $title = 'unexpected symbol';
    $location = $char->point->to_span();
    $report = Report::from_array([
      Report::title($title),
      Report::paragraph([
        'File contains a symbol that is not part of the syntax.',
        'Try removing it?',
      ]),
      Report::quote($program, $location),
    ]);

    return new Error($title, $location, $report);
  }

  public static function unclosed_string(string $program, Span $location): Error {
    $title = 'unclosed string';
    $report = Report::from_array([
      Report::title($title),
      Report::paragraph([
        'String extends to the end of a line without a closing double quote.',
      ]),
      Report::quote($program, $location),
    ]);

    return new Error($title, $location, $report);
  }
}
