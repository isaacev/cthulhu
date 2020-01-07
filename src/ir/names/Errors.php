<?php

namespace Cthulhu\ir\names;

use Cthulhu\err\Error;
use Cthulhu\ir\nodes;
use Cthulhu\loc\Spanlike;

class Errors {
  public static function unknown_name(Spanlike $spanlike, nodes\Name $name): Error {
    return (new Error('unknown name'))
      ->paragraph("There was a reference to a name that is not in the current scope.")
      ->snippet($spanlike);
  }

  public static function type_param_used_outside_function(Spanlike $spanlike): Error {
    return (new Error('type parameter used outside a function'))
      ->paragraph("Type parameters must be declared and used from within a function.")
      ->snippet($spanlike);
  }

  public static function unknown_type_param(Spanlike $spanlike, nodes\ParamNote $note): Error {
    return (new Error('unknown type parameter'))
      ->paragraph("The type parameter `'$note->name` could not be derived from the inputs to the current function.")
      ->snippet($spanlike);
  }

  public static function duplicate_union_type_parameter(Spanlike $spanlike, nodes\Name $name): Error {
    return (new Error('duplicate type parameter'))
      ->paragraph("Found a duplicate type parameter named `$name`:")
      ->snippet($spanlike);
  }

  public static function unknown_namespace_field(Spanlike $spanlike, nodes\Name $name): Error {
    return (new Error('unknown field'))
      ->paragraph("Reference to an unknown field in a namespace.")
      ->snippet($spanlike);
  }

  public static function unknown_constructor_form(Spanlike $spanlike, nodes\Ref $name): Error {
    return (new Error('unknown constructor form'))
      ->snippet($spanlike);
  }

  public static function unknown_constructor_field(Spanlike $spanlike, nodes\Ref $variant_name, nodes\Name $field_name): Error {
    return (new Error('unknown constructor field'))
      ->snippet($spanlike);
  }
}
