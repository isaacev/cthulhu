<?php

namespace Cthulhu\Debug;

use Cthulhu\lib\fmt\Formatter;

class Report {
  public $sections;

  function __construct(Reportable ...$sections) {
    $this->sections = $sections;
  }

  function append(Reportable $section): void {
    array_push($this->sections, $section);
  }

  public function format(Formatter $f): void {
    $f->increment_tab_stop(2);

    foreach ($this->sections as $section) {
      $section->print($f);
      $f->newline_if_not_already()
        ->newline();
    }

    $f->pop_tab_stop();
  }
}
