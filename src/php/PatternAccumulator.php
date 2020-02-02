<?php

namespace Cthulhu\php;

class PatternAccumulator {
  /* @var PatternContext[] $contextx */
  public array $contexts = [];

  public function push_pattern_context(nodes\Variable $discriminant): void {
    $context = new PatternContext($discriminant);
    array_push($this->contexts, $context);
  }

  public function peek_pattern_context(): PatternContext {
    assert(!empty($this->contexts));
    return end($this->contexts);
  }

  public function pop_pattern_context(): void {
    assert(!empty($this->contexts));
    $ctx = array_pop($this->contexts);
  }
}
