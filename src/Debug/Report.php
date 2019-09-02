<?php

namespace Cthulhu\Debug;

use Cthulhu\Parser\Lexer\Span;

class Report {
  const TOTAL_COLUMNS = 80;

  public $sections;

  function __construct(array $sections) {
    $this->sections = $sections;
  }

  public function __toString(): string {
    $options = new ReportOptions();
    return array_reduce($this->sections, function ($cursor, $section) use ($options) {
      return $section
        ->print($cursor, $options)
        ->reset()
        ->newline();
    }, new Cursor(2));
  }

  public static function from_array(array $sections): self {
    return new self($sections);
  }

  public static function title(string $title): Reportable {
    return new Title($title);
  }

  public static function paragraph(array $sentences): Reportable {
    return new Paragraph($sentences);
  }

  public static function quote(string $program, Span $location): Reportable {
    return new Quote(new Snippet($program, $location));
  }
}
