<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\types\Type;

class ListPattern extends Pattern {
  public array $members;
  public ?Glob $glob;

  /**
   * @param Type                $type
   * @param ListPatternMember[] $members
   * @param Glob|null           $glob
   */
  public function __construct(Type $type, array $members, ?Glob $glob) {
    parent::__construct($type);
    $this->members = $members;
    $this->glob    = $glob;
  }

  public function cardinality(): int {
    return count($this->members);
  }

  public function children(): array {
    return array_merge($this->members, [ $this->glob ]);
  }

  public function from_children(array $children): ListPattern {
    $members = array_slice($children, -1);
    $glob    = end($children);
    return new ListPattern($this->type, $members, $glob);
  }

  public function build(): Builder {
    return (new Builder);
  }

  public function __toString(): string {
    $glob = $this->glob ? " $this->glob " : "";
    if (empty($this->elements)) {
      return "[$glob]";
    } else {
      return "[ " . implode(", ", $this->elements) . $glob . "]";
    }
  }
}
