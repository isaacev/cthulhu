<?php

namespace Cthulhu\Source;

class File {
  public $filepath;
  public $contents;

  function __construct(string $filepath, string $contents) {
    $this->filepath = $filepath;
    $this->contents = $contents;
  }

  public function basename(): string {
    return basename($this->filepath);
  }
}
