<?php

namespace Cthulhu\ir;

use Cthulhu\err\Error;

class Errors {
  public static function no_main_func(): Error {
    return (new Error('no main function'))
      ->paragraph(
        "Without a main function the program won't run.",
        "A main function can be as simple as the following:"
      )
      ->example("#[entry]\nfn main() -> () {\n  -- more code here\n}");
  }
}
