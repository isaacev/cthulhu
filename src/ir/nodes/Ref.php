<?php

namespace Cthulhu\ir\nodes;

class Ref extends Node {
  public $extern;
  public $head_segments;
  public $tail_segment;

  function __construct(bool $extern, array $head_segments, Name $tail_segment) {
    parent::__construct();
    $this->extern = $extern;
    $this->head_segments = $head_segments;
    $this->tail_segment = $tail_segment;
  }

  function children(): array {
    return array_merge(
      $this->head_segments,
      [ $this->tail_segment ]
    );
  }

  function __toString(): string {
    $segments = implode('::', array_merge(
      $this->head_segments,
      [ $this->tail_segment ]
    ));

    if ($this->extern) {
      return '::' . $segments;
    } else {
      return $segments;
    }
  }
}
