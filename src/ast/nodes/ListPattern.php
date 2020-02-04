<?php

namespace Cthulhu\ast\nodes;

class ListPattern extends Pattern {
  public array $elements;
  public ?Glob $glob;

  /**
   * @param Pattern[] $elements
   * @param Glob|null $glob
   */
  public function __construct(array $elements, ?Glob $glob) {
    parent::__construct();
    $this->elements = $elements;
    $this->glob     = $glob;
  }

  public function children(): array {
    return $this->elements;
  }

  public function __toString(): string {
    if (empty($this->elements)) {
      return $this->glob ? "[ $this->glob ]" : "[]";
    } else {
      return "[ " . implode(", ", $this->elements) . ($this->glob ? " $this->glob " : "") . "]";
    }
  }
}
