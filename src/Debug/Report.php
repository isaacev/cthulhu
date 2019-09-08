<?php

namespace Cthulhu\Debug;

use Cthulhu\Parser\Lexer\Span;

class Report {
  public $sections;

  function __construct(Reportable ...$sections) {
    $this->sections = $sections;
  }

  function append(Reportable $section): void {
    array_push($this->sections, $section);
  }

  public function format(Teletype $tty): void {
    $tty->increase_tab_stop(2);

    foreach ($this->sections as $section) {
      $section->print($tty);
      $tty->newline_if_not_empty()->newline();
    }

    $tty->pop_tab_stop();
  }
}
