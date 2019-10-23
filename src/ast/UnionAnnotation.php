<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class UnionAnnotation extends Annotation {
  public $members;

  function __construct(Source\Span $span, array $members) {
    parent::__construct($span);
    $this->members = $members;
  }

  static function flatten(Annotation $left, Annotation $right): self {
    $span = $left->span->extended_to($right->span);
    $left_members = ($left instanceof self)
      ? $left->members
      : [ $left ];
    $right_members = ($right instanceof self)
      ? $right->members
      : [ $right ];
    return new self($span, array_merge($left_members, $right_members));
  }
}
