<?php

namespace Cthulhu\ast;

use Countable;
use Cthulhu\loc\Point;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

class TokenGroup extends TokenTree implements Countable, Spanlike {
  public string $delim;
  public Span $left;
  public array $members;
  public Span $right;

  /**
   * @param string      $delim
   * @param Spanlike    $left
   * @param TokenTree[] $members
   * @param Spanlike    $right
   */
  public function __construct(string $delim, Spanlike $left, array $members, Spanlike $right) {
    assert(in_array($delim, [ '{}', '[]', '()', '' ]));
    $this->delim   = $delim;
    $this->left    = $left->span();
    $this->members = $members;
    $this->right   = $right->span();
  }

  public function count(): int {
    return count($this->members);
  }

  public function span(): Span {
    return Span::join($this->left, $this->right);
  }

  public function from(): Point {
    return $this->left->from;
  }

  public function to(): Point {
    return $this->right->to;
  }
}
