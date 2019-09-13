<?php

namespace Cthulhu\Debug;

use Cthulhu\utils\fmt\Foreground;
use Cthulhu\utils\fmt\Formatter;

class Title implements Reportable {
  public $title;

  function __construct(string $title) {
    $this->title = $title;
  }

  public function print(Formatter $f): Formatter {
    return (
      $f->newline_if_not_already()
        ->tab()
        ->apply_styles(Foreground::RED)
        ->printf(strtoupper($this->title))
        ->spaces(2)
        ->fill_line('-', 2)
        ->reset_styles());
  }
}
