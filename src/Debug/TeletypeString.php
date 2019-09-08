<?php

namespace Cthulhu\Debug;

class TeletypeString extends Teletype {
  protected $buf = '';

  protected function write(string $str): void {
    $this->buf .= $str;
  }

  public function __toString(): string {
    return $this->buf;
  }
}
