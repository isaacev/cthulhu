<?php

namespace Cthulhu\Parser;

use Cthulhu\Debug;
use Cthulhu\Debug\Report;
use Cthulhu\Errors\Error;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;
use Cthulhu\Source;
use Cthulhu\lib\fmt\Foreground;

class Errors {
  public static function expected_item(Source\File $file, Token $found): Error {
    $title = 'expected item';
    return (new Error($file, $title, $found->span))
      ->snippet($found->span)
      ->paragraph('An item can be like one of the following:')
      ->example("-- import another module\nuse IO;")
      ->example("-- declare a module\nmod Example {\n  -- more stuff\n}")
      ->example("-- create a function\nfn hello() -> Str {\n  \"world\"\n}");
  }

  public static function exepcted_expression(Source\File $file, Token $found): Error {
    $title = 'expected expression';
    $found_desc = $found->description();
    return (new Error($file, $title, $found->span))
      ->paragraph("Found $found_desc instead.")
      ->snippet($found->span)
      ->paragraph('An expression can be like one of the following:')
      ->example('a + b * c')
      ->example('myFunction("hello")')
      ->example('if a { b; } else { c; }');
  }

  public static function expected_annotation(Source\File $file, Token $found): Error {
    $title = 'expected type annotation';
    $found_desc = $found->description();
    return (new Error($file, $title, $found->span))
      ->paragraph("Found $found_desc instead.")
      ->snippet($found->span)
      ->paragraph('A type annotation can be like one of the following:')
      ->example("Str")
      ->example("[Int]");
  }

  public static function expected_semicolon(Source\File $file, Source\Span $expected_loc): Error {
    $title = 'expected semicolon';
    return (new Error($file, $title, $expected_loc))
      ->paragraph('Reached the end of a statement and didn\'t find a semicolon.')
      ->snippet($expected_loc, 'try adding a semicolon here');
  }

  public static function expected_token(Source\File $file, Token $found, string $wanted_type): Error {
    $title = 'expected token';
    return (new Error($file, $title, $found->span))
      ->snippet($found->span, "expected $wanted_type here");
  }
}
