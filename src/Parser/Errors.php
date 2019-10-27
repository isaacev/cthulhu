<?php

namespace Cthulhu\Parser;

use Cthulhu\Errors\Error;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Source;

class Errors {
  public static function expected_item(Token $found): Error {
    return (new Error('expected item'))
      ->snippet($found->span)
      ->paragraph('An item can be like one of the following:')
      ->example("-- import another module\nuse Io;")
      ->example("-- declare a module\nmod Example {\n  -- more stuff\n}")
      ->example("-- create a function\nfn hello() -> Str {\n  \"world\"\n}");
  }

  public static function exepcted_expression(Token $found): Error {
    $found_desc = $found->description();
    return (new Error('expected expression'))
      ->paragraph("Found $found_desc instead.")
      ->snippet($found->span)
      ->paragraph('An expression can be like one of the following:')
      ->example('a + b * c')
      ->example('myFunction("hello")')
      ->example('if a { b; } else { c; }');
  }

  public static function expected_annotation(Token $found): Error {
    $found_desc = $found->description();
    return (new Error('expected type annotation'))
      ->paragraph("Found $found_desc instead.")
      ->snippet($found->span)
      ->paragraph('A type annotation can be like one of the following:')
      ->example("Str")
      ->example("[Int]");
  }

  public static function expected_semicolon(Source\Span $expected_loc): Error {
    return (new Error('expected semicolon'))
      ->paragraph('Reached the end of a statement and didn\'t find a semicolon.')
      ->snippet($expected_loc, 'try adding a semicolon here');
  }

  public static function expected_token(Token $found, string $wanted_type): Error {
    return (new Error('expected token'))
      ->snippet($found->span, "expected $wanted_type here");
  }
}
