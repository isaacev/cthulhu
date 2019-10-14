<?php

namespace Cthulhu\ir;

use Cthulhu\Errors\Error;
use Cthulhu\ir\nodes;

class Errors {
  public static function import_cycle(int $index, nodes\Library ...$libraries): Error {
    return (new Error('import cycle'))
      ->paragraph(
        "A library is indirectly importing itself.",
        "The import cycle contains these libraries:"
      )
      ->cycle($index, $libraries);
  }
}
