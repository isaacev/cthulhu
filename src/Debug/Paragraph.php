<?php

namespace Cthulhu\Debug;

use Cthulhu\lib\fmt\Formatter;

class Paragraph implements Reportable {
  public array $sentences;

  function __construct(array $sentences) {
    $this->sentences = $sentences;
  }

  public function print(Formatter $f): Formatter {
    return $f
      ->newline_if_not_already()
      ->tab()
      ->text_wrap(...$this->sentences);
  }
}
