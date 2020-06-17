<?php

namespace Cthulhu\lib\fmt;

class StreamFormatter extends Formatter {
  protected $resource;
  protected $use_color;

  /**
   * @param resource  $resource
   * @param bool|null $use_color
   */
  public function __construct($resource, ?bool $use_color = null) {
    assert(is_resource($resource));
    $this->resource  = $resource;
    $this->use_color = $use_color === null
      ? stream_isatty($resource)
      : $use_color;
  }

  protected function write(string $str): void {
    fwrite($this->resource, $str);
  }

  protected function use_color(): bool {
    return $this->use_color;
  }

  public static function stdout(?bool $use_color = null): self {
    return new self(STDOUT, $use_color);
  }

  public static function stderr(?bool $use_color = null): self {
    return new self(STDERR, $use_color);
  }
}
