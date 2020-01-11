<?php

namespace Cthulhu\ast;

use Cthulhu\err\Error;
use Cthulhu\loc\Spanlike;

class Errors {
  public static function unclosed_string(Spanlike $spanlike): Error {
    return (new Error('unclosed string'))
      ->paragraph('String extends to the end of a line without a closing double quote.')
      ->snippet($spanlike);
  }

  public static function unnamed_type_param(Spanlike $spanlike): Error {
    return (new Error('unnamed type parameter'))
      ->paragraph('Type parameters should look like `\'a`.')
      ->snippet($spanlike);
  }

  public static function unknown_escape_char(Spanlike $spanlike): Error {
    return (new Error('unknown escape character'))
      ->snippet($spanlike);
  }

  public static function expected_item(tokens\Token $found): Error {
    return (new Error('expected item'))
      ->snippet($found)
      ->paragraph('An item can be like one of the following:')
      ->example("-- import another module\nuse Io;")
      ->example("-- declare a module\nmod Example {\n  -- more stuff\n}")
      ->example("-- create a function\nfn hello() -> Str {\n  \"world\"\n}");
  }

  public static function expected_pattern(tokens\Token $found): Error {
    return (new Error('expected pattern'))
      ->snippet($found)
      ->paragraph('A pattern can be like one of the following:')
      ->example('_')
      ->example('"abc"')
      ->example('Maybe::Just(x)');
  }

  public static function expected_note(tokens\Token $found): Error {
    return (new Error('expected type annotation'))
      ->snippet($found)
      ->paragraph('A type annotation can be like one of the following:')
      ->example("Str")
      ->example("[Int]");
  }

  public static function expected_expression(tokens\Token $found): Error {
    return (new Error('expected expression'))
      ->snippet($found)
      ->paragraph('An expression can be like one of the following:')
      ->example('a + b * c')
      ->example('myFunction("hello")')
      ->example('if a { b; } else { c; }');
  }

  public static function used_reserved_ident(tokens\Token $found): Error {
    return (new Error('use of reserved word'))
      ->snippet($found, 'reserved words cannot be used as identifiers');
  }

  public static function expected_token(Spanlike $found, string $description): Error {
    return (new Error('expected token'))
      ->snippet($found, "expected $description here");
  }
}
