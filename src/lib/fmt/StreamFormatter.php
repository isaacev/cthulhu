<?php

namespace Cthulhu\lib\fmt;

class StreamFormatter extends Formatter {
  protected $resource;
  protected $use_color;

  function __construct($resource, ?bool $use_color = null) {
    if (is_resource($resource) === false) {
      throw new \Exception('StreamFormatter requires a resource object');
    }

    $this->resource  = $resource;
    $this->use_color = $use_color === null
      ? posix_isatty($resource)
      : $use_color;
  }

  protected function write(string $str): void {
    fwrite($this->resource, $str);
  }

  protected function use_color(): bool {
    return $this->use_color;
  }
}
