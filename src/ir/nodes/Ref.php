<?php

namespace Cthulhu\ir\nodes;

class Ref extends Node {
  public bool $extern;
  public array $head_segments;
  public Name $tail_segment;

  /**
   * @param bool   $extern
   * @param Name[] $head_segments
   * @param Name   $tail_segment
   */
  public function __construct(bool $extern, array $head_segments, Name $tail_segment) {
    parent::__construct();
    $this->extern        = $extern;
    $this->head_segments = $head_segments;
    $this->tail_segment  = $tail_segment;
  }

  public function children(): array {
    return array_merge(
      $this->head_segments,
      [ $this->tail_segment ]
    );
  }

  public function equals(self $other): bool {
    return $this->tail_segment->get('symbol')->equals($other->tail_segment->get('symbol'));
  }

  public function __toString(): string {
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
