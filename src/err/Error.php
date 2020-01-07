<?php

namespace Cthulhu\err;

use Cthulhu\Debug;
use Cthulhu\lib\fmt\Formatter;
use Cthulhu\loc\Spanlike;
use Exception;

class Error extends Exception {
  private string $title;
  private Debug\Report $report;

  public function __construct(string $title) {
    parent::__construct($title);
    $this->title  = $title;
    $this->report = new Debug\Report(
      new Debug\Title($title)
    );
  }

  public function snippet(Spanlike $spanlike, ?string $message = null, array $options = []): self {
    $file = $spanlike->from()->file;
    $this->report->append(new Debug\Snippet($file, $spanlike, $message, $options));
    return $this;
  }

  public function paragraph(string ...$sentences): self {
    $this->report->append(new Debug\Paragraph($sentences));
    return $this;
  }

  public function example(string $example): self {
    $this->report->append(new Debug\Example($example));
    return $this;
  }

  public function cycle(int $index, array $members): self {
    $this->report->append(new Debug\Cycle($index, $members));
    return $this;
  }

  public function format(Formatter $f): void {
    $this->report->format($f);
  }
}

