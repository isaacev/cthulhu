<?php

namespace Cthulhu\Parser;

use Cthulhu\Debug\Report;
use Cthulhu\Errors\Error;
use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Parser\Lexer\Token;

class Errors {
  public static function expected_item(string $program, Token $found): Error {
    $title = 'expected item, found ' . $found->description();
    $location = $found->span;
    $report = Report::from_array([
      Report::title($title),
      Report::quote($program, $location),
    ]);

    return new Error($title, $location, $report);
  }

  public static function expected_statement(string $program, Token $found): Error {
    $title = 'expected statement, found ' . $found->description();
    $location = $found->span;
    $report = Report::from_array([
      Report::title($title),
      Report::quote($program, $location),
    ]);

    return new Error($title, $location, $report);
  }

  public static function exepcted_expression(string $program, Token $found): Error {
    $title = 'expected expression, found ' . $found->description();
    $location = $found->span;
    $report = Report::from_array([
      Report::title($title),
      Report::quote($program, $location),
    ]);

    return new Error($title, $location, $report);
  }

  public static function expected_annotation(string $program, Token $found): Error {
    $title = 'expected a type annotation, found ' . $found->description();
    $location = $found->span;
    $report = Report::from_array([
      Report::title($title),
      Report::quote($program, $location),
    ]);

    return new Error($title, $location, $report);
  }

  public static function expected_token(string $program, Token $found, string $wanted_type): Error {
    $wanted_desc = "`$wanted_type`";
    $found_desc = $found->description();
    $title = "expected a $wanted_desc token, found $found_desc instead";
    $location = $found->span;
    $report = Report::from_array([
      Report::title($title),
      Report::quote($program, $location),
    ]);

    return new Error($title, $location, $report);
  }
}
