<?php

namespace Cthulhu\ir\patterns;

class ListPattern extends Pattern {
  public array $elements;
  public bool $has_glob;

  /**
   * @param Pattern[] $elements
   * @param bool      $has_glob
   */
  public function __construct(array $elements, bool $has_glob) {
    $this->elements = $elements;
    $this->has_glob = $has_glob;
  }

  public function cardinality(): int {
    return count($this->elements);
  }

  public function __toString(): string {
    if (empty($this->elements)) {
      return $this->has_glob ? "[ ... ]" : "[]";
    } else {
      $str = "";
      if (!empty($this->elements)) {
        foreach ($this->elements as $index => $element) {
          $str .= ($index === 0) ? '' : ', ';
          $str .= "$element";
        }
      }

      if ($this->has_glob) {
        $str .= empty($str) ? '' : ', ';
        $str .= '...';
      }

      return "[ $str ]";
    }
  }
}
