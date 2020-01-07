<?php

namespace Cthulhu\ir;

use Cthulhu\err\Error;
use Cthulhu\ir\nodes;
use Cthulhu\loc\Spanlike;

class Errors {
  public static function import_cycle(int $index, nodes\Library ...$libraries): Error {
    return (new Error('import cycle'))
      ->paragraph(
        "A library is indirectly importing itself.",
        "The import cycle contains these libraries:"
      )
      ->cycle($index, $libraries);
  }

  public static function redundant_named_fields(Spanlike $first, Spanlike $second, string $name): Error {
    return (new Error('redundant named fields'))
      ->paragraph(
        "There are more than one fields named `$name`.",
        "The first usage is here:"
      )
      ->snippet($first)
      ->paragraph("The second usage is here:")
      ->snippet($second);
  }

  public static function redundant_pattern(Spanlike $spanlike, patterns\Pattern $pattern): Error {
    return (new Error('redundant pattern'))
      ->paragraph("The pattern `$pattern` will never be matched because all values in its domain will be handled by prior patterns.")
      ->snippet($spanlike);
  }

  public static function uncovered_patterns(Spanlike $spanlike, array $patterns): Error {
    $n   = count($patterns);
    $err = (new Error('uncovered patterns'))
      ->paragraph('The match expression does not handle all possible patterns.')
      ->snippet($spanlike)
      ->paragraph('The following ' . ($n === 1 ? 'pattern' : "$n patterns") . ' were not handled:');

    foreach ($patterns as $pattern) {
      $err->example("$pattern");
    }

    return $err;
  }
}
