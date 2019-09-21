<?php

namespace Cthulhu\Errors;

use Cthulhu\Debug;
use Cthulhu\Source;
use Cthulhu\lib\fmt\Formatter;

class Error extends \Exception {
  private $source_file;
  private $title;
  private $location;
  private $report;

  function __construct(Source\File $source_file, string $title, Source\Span $location) {
    parent::__construct("$title at $location->from");
    $this->source_file = $source_file;
    $this->title = $title;
    $this->location = $location;
    $this->report = new Debug\Report(
      new Debug\Title($title)
    );
  }

  public function snippet(Source\Span $location, ?string $message = null, array $options = []): self {
    $this->report->append(new Debug\Snippet($this->source_file, $location, $message, $options));
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

  public function format(Formatter $f): void {
    $this->report->format($f);
  }
}
