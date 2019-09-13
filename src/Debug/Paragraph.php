<?php

namespace Cthulhu\Debug;

use Cthulhu\utils\fmt\Formatter;

class Paragraph implements Reportable {
  const MAX_LINE_LENGTH = 80 - 4;

  public $sentences;

  function __construct(array $sentences) {
    $this->sentences = $sentences;
  }

  public function print(Formatter $f): Formatter {
    return (
      $f->newline_if_not_already()
        ->tab()
        ->text_wrap(...$this->sentences));
  }
}
