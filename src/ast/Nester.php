<?php

namespace Cthulhu\ast;

use Cthulhu\ast\tokens\DelimToken;
use Cthulhu\ast\tokens\TerminalToken;
use Cthulhu\err\Error;
use Cthulhu\lib\panic\Panic;
use Cthulhu\loc\Point;

class Nester {
  private Lexer $lexer;

  public function __construct(Lexer $lexer) {
    $this->lexer = $lexer;
  }

  /**
   * @return TokenGroup
   * @throws Error
   * @noinspection PhpInconsistentReturnPointsInspection
   */
  public function nest(): TokenGroup {
    /* @var DelimToken[] $left_stack */
    $left_stack = [];

    /* @var TokenTree[][] $member_stack */
    $member_stack = [ [] ];

    while ($next = $this->lexer->next()) {
      if ($next instanceof DelimToken) {
        if ($next->is_left()) {
          $left_stack[] = $next;
          array_push($member_stack, []);
        } else if (empty($left_stack)) {
          throw Errors::unexpected_right_delim($next);
        } else {
          $left = array_pop($left_stack);
          if ($left->balanced_with($next)) {
            $members   = array_pop($member_stack);
            $delim     = $left->lexeme . $next->lexeme;
            $new_group = new TokenGroup($delim, $left, $members, $next);
            array_push($member_stack[count($member_stack) - 1], $new_group);
          } else {
            throw Errors::unbalanced_delim($left, $next);
          }
        }
      } else if ($next instanceof TerminalToken) {
        if (empty($left_stack)) {
          assert(count($member_stack) === 1);
          $left    = (new Point($next->span->from->file, 1, 1))->span();
          $delim   = '';
          $members = array_pop($member_stack);
          return new TokenGroup($delim, $left, $members, $next);
        } else {
          throw Errors::unbalanced_delim(end($left_stack), $next);
        }
      } else {
        $offset   = count(end($member_stack));
        $new_leaf = new TokenLeaf($offset, $next);
        array_push($member_stack[count($member_stack) - 1], $new_leaf);
      }
    }
    Panic::if_reached(__LINE__, __FILE__);
  }
}
