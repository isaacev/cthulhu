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

  public static function unknown_namespace_field(Source\Span $span, nodes\Name $name): Error {
    return (new Error('unknown field'))
      ->paragraph("Reference to an unknown field in a namespace.")
      ->snippet($span);
  }
}
