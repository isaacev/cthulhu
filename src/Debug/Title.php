<?php

namespace Cthulhu\Debug;

class Title implements Reportable {
  public $title;

  function __construct(string $title) {
    $this->title = $title;
  }

  public function print(Cursor $cursor, ReportOptions $options): Cursor {
    return $cursor
      ->reset()
      ->newline()
      ->spaces(1)
      ->inverse()
      ->spaces(1)
      ->bold()
      ->text($this->title)
      ->spaces(1)
      ->reset()
      ->spaces(2)
      ->repeat('-', 80 - 7 - strlen($this->title))
      ->newline();
  }
}
