<?php

namespace Cthulhu\ir\patterns;

use Cthulhu\ir\types\ListType;
use Cthulhu\ir\types\Type;

class ListNode extends Node {
  protected Type $element_type;

  /* @var Sum[] $table */
  protected array $table = [];

  protected bool $has_wildcard = false;

  public function __construct(ListType $type) {
    $this->element_type = $type->elements;
  }

  public function is_covered(): bool {
    if ($this->has_wildcard) {
      return true;
    }

    $has_glob = false;
    $max_card = $this->max_known_cardinality();
    for ($card = 0; $card <= $max_card; $card++) {
      if (array_key_exists($card, $this->table)) {
        $has_glob = $this->table[$card]->has_glob || $has_glob;
        foreach ($this->table[$card]->members as $node) {
          if ($node->is_covered() === false) {
            return false;
          }
        }
      } else {
        return false;
      }
    }

    return $has_glob;
  }

  public function is_redundant(Pattern $pattern): bool {
    if ($this->is_covered()) {
      return true;
    } else if ($pattern instanceof WildcardPattern) {
      return false;
    }

    assert($pattern instanceof ListPattern);

    $pat_card = $pattern->cardinality();

    /**
     * Check if any of the previous patterns with a smaller cardinality but with
     * a glob would make this pattern redundant.
     */
    for ($card = 0; $card < $pat_card; $card++) {
      if (array_key_exists($card, $this->table) && $this->table[$card]->has_glob) {
        $all_covered = true;
        for ($i = 0; $i <= $card; $i++) {
          if (!$this->table[$card]->members[$i]->is_redundant($pattern->elements[$i])) {
            $all_covered = false;
            break;
          }
        }

        if ($all_covered) {
          return true;
        }
      }
    }

    /**
     * Check if previous patterns with the same cardinality make this pattern
     * redundant.
     */
    if (array_key_exists($pat_card, $this->table)) {
      if ($this->table[$pat_card]->has_glob === false && $pattern->has_glob) {
        return false;
      }

      $all_covered = true;
      for ($i = 0; $i < $pat_card; $i++) {
        if (!$this->table[$pat_card]->members[$i]->is_redundant($pattern->elements[$i])) {
          $all_covered = false;
          break;
        }
      }

      if ($all_covered) {
        return true;
      }
    }

    /**
     * If the pattern has a glob, make sure that previous patterns with a larger
     * cardinality and a glob make the pattern redundant.
     */
    if ($pattern->has_glob) {
      for ($card = $pat_card + 1; $card <= $this->max_known_cardinality(); $card++) {
        if (array_key_exists($card, $this->table) && $this->table[$card]->has_glob) {
          $all_covered = true;
          for ($i = 0; $i < $card; $i++) {
            $sub_pattern = ($i < $pat_card) ? $pattern->elements[$i] : new WildcardPattern();
            if (!$this->table[$card]->members[$i]->is_redundant($sub_pattern)) {
              $all_covered = false;
              break;
            }
          }

          if ($all_covered) {
            return true;
          }
        }
      }
    }

    return false;
  }

  private function max_known_cardinality(): int {
    if (empty($this->table)) {
      return -1;
    } else {
      return max(array_keys($this->table));
    }
  }

  private function create_node_for_cardinality(int $cardinality): void {
    $members = [];
    for ($i = 0; $i < $cardinality; $i++) {
      $members[] = Node::from_type($this->element_type);
    }
    $this->table[$cardinality] = new Sum($members);
  }

  public function apply(Pattern $pattern): void {
    if ($pattern instanceof WildcardPattern) {
      $this->has_wildcard = true;
      return;
    }

    assert($pattern instanceof ListPattern);

    $lo_cardinality = $pattern->cardinality();
    $hi_cardinality = $pattern->has_glob ? $this->max_known_cardinality() : $lo_cardinality;

    do {
      if (array_key_exists($lo_cardinality, $this->table) === false) {
        $this->create_node_for_cardinality($lo_cardinality);
      }
      $this->table[$lo_cardinality++]->apply($pattern);
    } while ($lo_cardinality <= $hi_cardinality);
  }

  public function uncovered_patterns(): array {
    $patterns    = [];
    $covers_glob = false;
    for ($i = 0; $i <= $this->max_known_cardinality(); $i++) {
      $is_cardinality_covered = true;
      if (array_key_exists($i, $this->table)) {
        $covers_glob = $this->table[$i]->has_glob || $covers_glob;
        foreach ($this->table[$i]->members as $sub) {
          if ($sub->is_covered() === false) {
            $is_cardinality_covered = false;
          }
        }
      } else {
        $is_cardinality_covered = false;
      }

      if ($is_cardinality_covered === false) {
        $wildcards = [];
        for ($j = 0; $j < $i; $j++) {
          $wildcards[] = new WildcardPattern();
        }
        $patterns[] = new ListPattern($wildcards, false);
      }
    }

    if ($covers_glob === false) {
      $wildcards = [];
      for ($j = 0; $j < $this->max_known_cardinality(); $j++) {
        $wildcards[] = new WildcardPattern();
      }
      $patterns[] = new ListPattern($wildcards, true);
    }

    return $patterns;
  }
}
