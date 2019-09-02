<?php

namespace Cthulhu\Errors;

use Cthulhu\Debug\Report;
use Cthulhu\Parser\Lexer\Span;

class Error extends \Exception {
  protected $title;
  protected $location;
  protected $report;

  function __construct(string $title, Span $location, Report $report) {
    parent::__construct("$title at $location->from");
    $this->title = $title;
    $this->location = $location;
    $this->report = $report;
  }

  public function __toString(): string {
    return $this->report;
  }
}
