<?php

namespace Cthulhu\Debug;

use Cthulhu\lib\fmt\Foreground;
use Cthulhu\lib\fmt\Formatter;

class Title implements Reportable {
  public string $title;

  function __construct(string $title) {
    $this->title = $title;
  }

  public function print(Formatter $f): Formatter {
    return $f
      ->newline_if_not_already()
      ->tab()
      ->apply_styles(Foreground::RED)
      ->print(strtoupper($this->title))
      ->spaces(2)
      ->fill_line('-', 2)
      ->reset_styles();
  }
}
