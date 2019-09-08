<?php

namespace Cthulhu\Debug;

class Title implements Reportable {
  public $title;

  function __construct(string $title) {
    $this->title = $title;
  }

  public function print(Teletype $tty): Teletype {
    return $tty
      ->newline_if_not_empty()
      ->tab()
      ->apply_styles(Foreground::RED)
      ->printf(strtoupper($this->title))
      ->spaces(2)
      ->fill_line('-', 2)
      ->reset_styles();
  }
}
