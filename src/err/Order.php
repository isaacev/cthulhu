<?php

namespace Cthulhu\err;

use Cthulhu\lib\fmt\Formatter;

class Order implements Reportable {
  public array $things;

  /**
   * @param string[] $things
   */
  public function __construct(array $things) {
    $this->things = $things;
  }

  public function print(Formatter $f): ?Formatter {
    if (empty($this->things)) {
      return null;
    }

    $f->increment_tab_stop(2);

    foreach ($this->things as $thing) {
      $f->newline_if_not_already()
        ->tab()
        ->print($thing);
    }

    return $f->pop_tab_stop();
  }
}
