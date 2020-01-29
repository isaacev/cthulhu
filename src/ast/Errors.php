<?php

namespace Cthulhu\ast;

use Cthulhu\ast\tokens\DelimToken;
use Cthulhu\ast\tokens\Token;
use Cthulhu\err\Error;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

class Errors {
  public static function unable_to_read_file(string $filepath): Error {
    return (new Error('unable to read file'))
      ->paragraph("Either the following file does not exist or this process is not allowed to read it:")
      ->paragraph($filepath);
  }

  public static function unknown_library(string $name): Error {
    return (new Error('unknown library'))
      ->paragraph("Library not found in the standard library or in a nearby directory:")
      ->example("use ::$name;");
  }

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

  public static function unexpected_right_delim(DelimToken $right): Error {
    return (new Error('unbalanced right delimiter'))
      ->snippet($right);
  }

  public static function unbalanced_delim(DelimToken $left, Token $right): Error {
    return (new Error('unbalanced delimiter'))
      ->snippet(Span::join($left, $right));
  }

  public static function wrong_attr_arity(nodes\Attribute $attr, int $wanted_arity): Error {
    return (new Error('wrong number of attribute arguments'))
      ->paragraph("Expected $wanted_arity arguments.")
      ->snippet($attr->get('span'));
  }

  public static function unknown_attr_arg(nodes\LowerName $arg): Error {
    return (new Error('unknown attribute argument'))
      ->snippet($arg->get('span'));
  }

  public static function missing_precedence_attr(Spanlike $span): Error {
    return (new Error('missing precedence for operator'))
      ->paragraph("No 'precedence' attribute found:")
      ->snippet($span)
      ->paragraph("Functions that define a new operator must have a 'precedence' attribute:")
      ->example("#[precedence(sum)]\nfn +> custom_adder(a: Int, b: Int) -> Int {\n  -- code\n}");
  }

  public static function wrong_prec_arity(Spanlike $span, int $min_arity, int $found_arity): Error {
    return (new Error('wrong operator arity'))
      ->paragraph(
        "Expected the function to accept at least $min_arity parameters.",
        "Instead the function accepts only $found_arity"
      )
      ->snippet($span);
  }

  public static function expected_item(Spanlike $span): Error {
    return (new Error('expected item'))
      ->snippet($span)
      ->paragraph('An item can be like one of the following:')
      ->example("-- import another module\nuse Io;")
      ->example("-- declare a module\nmod Example {\n  -- more stuff\n}")
      ->example("-- create a function\nfn hello() -> Str {\n  \"world\"\n}");
  }

  public static function expected_pattern(Spanlike $spanlike): Error {
    return (new Error('expected pattern'))
      ->snippet($spanlike)
      ->paragraph('A pattern can be like one of the following:')
      ->example('_')
      ->example('"abc"')
      ->example('Maybe::Just(x)');
  }

  public static function expected_note(Spanlike $spanlike): Error {
    return (new Error('expected type annotation'))
      ->snippet($spanlike)
      ->paragraph('A type annotation can be like one of the following:')
      ->example("Str")
      ->example("[Int]");
  }

  public static function expected_expression(Spanlike $spanlike): Error {
    return (new Error('expected expression'))
      ->snippet($spanlike)
      ->paragraph('An expression can be like one of the following:')
      ->example('a + b * c')
      ->example('myFunction("hello")')
      ->example('if a { b; } else { c; }');
  }

  public static function used_reserved_ident(Spanlike $spanlike): Error {
    return (new Error('use of reserved word'))
      ->snippet($spanlike, 'reserved words cannot be used as identifiers');
  }

  public static function expected_token(Spanlike $found, string $description): Error {
    return (new Error('expected token'))
      ->snippet($found, "expected $description here");
  }

  /**
   * @param int                 $index
   * @param nodes\ShallowFile[] $libs
   * @return Error
   */
  public static function import_cycle(int $index, array $libs): Error {
    return (new Error('import cycle'))
      ->paragraph(
        "A library is indirectly importing itself.",
        "The import cycle contains these libraries:"
      )
      ->cycle($index, $libs);
  }

  public static function unknown_name(Spanlike $spanlike): Error {
    return (new Error('unknown name'))
      ->paragraph("There was a reference to a name that is not in the current scope.")
      ->snippet($spanlike);
  }

  public static function unknown_namespace_field(Spanlike $spanlike): Error {
    return (new Error('unknown field'))
      ->paragraph("Reference to an unknown field in a namespace.")
      ->snippet($spanlike);
  }

  public static function unknown_type_param(Spanlike $spanlike, nodes\TypeParamNote $note): Error {
    return (new Error('unknown type parameter'))
      ->paragraph("The type parameter `'$note->name` could not be derived from the inputs to the current function.")
      ->snippet($spanlike);
  }

  public static function duplicate_enum_param(Spanlike $spanlike, string $name): Error {
    return (new Error('duplicate type parameter'))
      ->paragraph("Found a duplicate type parameter named `$name`:")
      ->snippet($spanlike);
  }

  public static function unknown_form_field(Spanlike $spanlike, string $name): Error {
    return (new Error('unknown form field'))
      ->paragraph("The form doesn't have a field named `$name`:")
      ->snippet($spanlike);
  }

  public static function duplicate_field_binding(nodes\NamePatternPair $pair): Error {
    return (new Error('duplicate field'))
      ->paragraph("The field named '$pair->name' was already bound to a pattern")
      ->snippet($pair->get('span'));
  }

  public static function missing_field_binding(Spanlike $spanlike, string $name): Error {
    return (new Error('missing field'))
      ->paragraph("The pattern didn't match against the field named '$name'")
      ->snippet($spanlike);
  }
}
