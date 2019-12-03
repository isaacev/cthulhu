<?php

namespace Cthulhu\ir;

use Cthulhu\Errors\Error;
use Cthulhu\ir\nodes;
use Cthulhu\Source;

class Errors {
  public static function import_cycle(int $index, nodes\Library ...$libraries): Error {
    return (new Error('import cycle'))
      ->paragraph(
        "A library is indirectly importing itself.",
        "The import cycle contains these libraries:"
      )
      ->cycle($index, $libraries);
  }

  public static function redundant_named_fields(Source\Span $first, Source\Span $second, string $name): Error {
    return (new Error('redundant named fields'))
      ->paragraph(
        "There are more than one fields named `$name`.",
        "The first usage is here:"
      )
      ->snippet($first)
      ->paragraph("The second usage is here:")
      ->snippet($second);
  }

  public static function redundant_pattern(Source\Span $span, patterns\Pattern $pattern): Error {
    return (new Error('redundant pattern'))
      ->paragraph("The pattern `$pattern` will never be matched because all values in its domain will be handled by prior patterns.")
      ->snippet($span);
  }

  public static function uncovered_patterns(Source\Span $span, array $patterns): Error {
    $n   = count($patterns);
    $err = (new Error('uncovered patterns'))
      ->paragraph('The match expression does not handle all possible patterns.')
      ->snippet($span)
      ->paragraph('The following ' . ($n === 1 ? 'pattern' : "$n patterns") . ' were not handled:');

    foreach ($patterns as $pattern) {
      $err->example("$pattern");
    }

    return $err;
  }
}
