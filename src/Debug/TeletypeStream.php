<?php

namespace Cthulhu\Debug;

class TeletypeStream extends Teletype {
  protected $resource;

  function __construct($resource, array $options = []) {
    parent::__construct($options);
    $this->resource = $resource;
  }

  protected function write(string $str): void {
    fwrite($this->resource, $str);
  }
}
