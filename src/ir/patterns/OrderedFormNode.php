<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types;

class OrderedFormNode extends FormNode {
  public const PERMUTATION_CUTOFF = 12;

  /* @var Node[] */
  protected array $child_nodes = [];
  protected types\Tuple $type;

  public function __construct(string $name, types\Tuple $type) {
    parent::__construct($name);
    $this->type = $type;
  }

  public function is_covered(): bool {
    foreach ($this->type->members as $index => $type) {
      if (array_key_exists($index, $this->child_nodes)) {
        if ($this->child_nodes[$index]->is_covered() === false) {
          return false;
        }
      } else {
        return false;
      }
    }
    return true;
  }

  public function is_redundant(Pattern $pattern): bool {
    assert($pattern instanceof FormPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof OrderedFormFields);
    foreach ($pattern->fields->order as $index => $sub_pattern) {
      if (array_key_exists($index, $this->child_nodes)) {
        if ($this->child_nodes[$index]->is_redundant($sub_pattern) === false) {
          return false;
        }
      } else {
        return false;
      }
    }
    return true;
  }

  public function apply(Pattern $pattern): void {
    assert($pattern instanceof FormPattern);
    assert($pattern->name === $this->name);
    assert($pattern->fields instanceof OrderedFormFields);
    foreach ($pattern->fields->order as $index => $sub_pattern) {
      if (!array_key_exists($index, $this->child_nodes)) {
        $this->child_nodes[$index] = Node::from_type($this->type->members[$index]);
      }
      $this->child_nodes[$index]->apply($sub_pattern);
    }
  }

  /**
   * @return FormPattern[]
   */
  public function uncovered_patterns(): array {
    if ($this->is_covered()) {
      return [];
    }

    $uncovered_child_patterns = [];
    $total_permutations       = 0;
    foreach ($this->child_nodes as $index => $child_pattern) {
      $uncovered_child_patterns[$index] = $subset = $child_pattern->uncovered_patterns();
      if (empty($subset)) {
        continue;
      } else {
        $total_permutations = ($total_permutations === 0)
          ? count($subset)
          : $total_permutations * count($subset);
      }
    }

    if ($total_permutations < self::PERMUTATION_CUTOFF) {
      $iterator = [];
      $cutoff   = [];
      foreach ($uncovered_child_patterns as $index => $subset) {
        $iterator[$index] = 0;
        $cutoff[$index]   = count($subset) - 1;
      }

      $permutations = [];
      while (true) {
        $order = [];
        foreach ($iterator as $i => $j) {
          $order[$i] = $uncovered_child_patterns[$i][$j];
        }
        $permutations[] = new FormPattern($this->name, new OrderedFormFields($order));
        $overflow_flag  = true;
        for ($i = count($iterator) - 1; $i >= 0; $i--) {
          if ($overflow_flag) {
            $iterator[$i]++;
            $overflow_flag = false;
          }

          if ($iterator[$i] > $cutoff[$i] || $overflow_flag) {
            $iterator[$i]  = 0;
            $overflow_flag = true;
          }
        }

        if ($overflow_flag) {
          break;
        }
      }

      return $permutations;
    } else {
      $order = [];
      foreach ($this->type->members as $index => $type) {
        $order[$index] = new WildcardPattern();
      }
      $fields = new OrderedFormFields($order);
      return [ new FormPattern($this->name, $fields) ];
    }
  }
}
