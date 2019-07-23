<?php

namespace Cthulhu\Parser\AST;

use Cthulhu\Parser\Lexer\Point;

class NamedAnnotation extends Annotation {
  public $from;
  public $name;

  function __construct(Point $from, string $name) {
    $this->from = $from;
    $this->name = $name;
  }

  /**
   * @codeCoverageIgnore
   */
  public function from(): Point {
    return $this->from;
  }

  public function jsonSerialize() {
    return [
      'type' => 'NamedAnnotation',
      'name' => $this->name
    ];
  }
}
