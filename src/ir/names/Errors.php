<?php

namespace Cthulhu\ir\names;

use Cthulhu\Errors\Error;
use Cthulhu\ir\nodes;
use Cthulhu\Source;

class Errors {
  public static function unknown_name(Source\Span $span, nodes\Name $name): Error {
    return (new Error('unknown name'))
      ->paragraph("There was a reference to a name that is not in the current scope.")
      ->snippet($span);
  }

  public static function type_param_used_outside_function(Source\Span $span): Error {
    return (new Error('type parameter used outside a function'))
      ->paragraph("Type parameters must be declared and used from within a function.")
      ->snippet($span);
  }

  public static function unknown_type_param(Source\Span $span, nodes\ParamNote $note): Error {
    return (new Error('unknown type parameter'))
      ->paragraph("The type parameter `'$note->name` could not be derived from the inputs to the current function.")
      ->snippet($span);
  }

  public static function unknown_namespace_field(Source\Span $span, nodes\Name $name): Error {
    return (new Error('unknown field'))
      ->paragraph("Reference to an unknown field in a namespace.")
      ->snippet($span);
  }

  public static function unknown_constructor_form(Source\Span $span, nodes\Ref $name): Error {
    return (new Error('unknown constructor form'))
      ->snippet($span);
  }

  public static function unknown_constructor_field(Source\Span $span, nodes\Ref $variant_name, nodes\Name $field_name): Error {
    return (new Error('unknown constructor field'))
      ->snippet($span);
  }
}
