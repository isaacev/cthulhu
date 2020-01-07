<?php

namespace Cthulhu\ir;

use Cthulhu\err\Error;

class Flow {
  private array $match_types = [];
  private array $coverage_trees = [];

  private function push_pattern_tree(types\Type $type) {
    array_push($this->coverage_trees, patterns\Node::from_type($type));
  }

  private function peek_pattern_tree(): patterns\Node {
    return end($this->coverage_trees);
  }

  private function pop_pattern_tree(): patterns\Node {
    return array_pop($this->coverage_trees);
  }

  /**
   * @param nodes\Program $prog
   * @throws Error
   * @noinspection PhpDocRedundantThrowsInspection
   */
  public static function analyze(nodes\Program $prog): void {
    $ctx = new self();

    Visitor::walk($prog, [
      'enter(MatchExpr)' => function (nodes\MatchExpr $expr) use ($ctx) {
        self::enter_match_expr($ctx, $expr);
      },
      'MatchArm' => function (nodes\MatchArm $arm) use ($ctx) {
        self::match_arm($ctx, $arm);
      },
      'exit(MatchExpr)' => function (nodes\MatchExpr $expr) use ($ctx) {
        self::exit_match_expr($ctx, $expr);
      },
    ]);
  }

  private static function enter_match_expr(self $ctx, nodes\MatchExpr $expr): void {
    array_push($ctx->match_types, $expr->disc->expr->get('type'));
    $ctx->push_pattern_tree($expr->disc->expr->get('type'));
  }

  /**
   * @param Flow           $ctx
   * @param nodes\MatchArm $arm
   * @throws Error
   */
  private static function match_arm(self $ctx, nodes\MatchArm $arm): void {
    $type    = end($ctx->match_types);
    $pattern = patterns\Pattern::from($arm->pattern, $type);
    if ($ctx->peek_pattern_tree()->is_redundant($pattern)) {
      $span = $arm->pattern->get('span');
      throw Errors::redundant_pattern($span, $pattern);
    } else {
      $ctx->peek_pattern_tree()->apply($pattern);
    }
  }

  /**
   * @param Flow            $ctx
   * @param nodes\MatchExpr $expr
   * @throws Error
   */
  private static function exit_match_expr(self $ctx, nodes\MatchExpr $expr): void {
    array_pop($ctx->match_types);
    $uncovered = $ctx->pop_pattern_tree()->uncovered_patterns();
    if (!empty($uncovered)) {
      $span = $expr->get('span');
      throw Errors::uncovered_patterns($span, $uncovered);
    }
  }
}
