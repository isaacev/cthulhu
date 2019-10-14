<?php

namespace Cthulhu\ast;

use Cthulhu\Source;

class TupleAnnotation extends Annotation {
  public $members;

  function __construct(Source\Span $span, array $members) {
    parent::__construct($span);
    assert(!empty($members));
    $this->members = $members;
  }

  public function visit(array $visitor_table): void {
    if (array_key_exists('TupleAnnotation', $visitor_table)) {
      $visitor_table['TupleAnnotation']($this);
    }

    foreach ($this->members as $member) {
      $member->visit($visitor_table);
    }
  }

  public function jsonSerialize() {
    return [
      'type' => 'TupleAnnotation',
      'members' => $this->members,
    ];
  }
}
