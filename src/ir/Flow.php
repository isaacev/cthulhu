<?php

namespace Cthulhu\ir;

class Flow {
  private $spans;
  private $types;
  private $match_types = [];
  private $coverage_trees = [];

  private function __construct(Table $spans, Table $types) {
    $this->spans = $spans;
    $this->types = $types;
  }

  private function push_pattern_tree(types\Type $type) {
    array_push($this->coverage_trees, patterns\Node::from_type($type));
  }

  private function peek_pattern_tree(): patterns\Node {
    return end($this->coverage_trees);
  }

  private function pop_pattern_tree(): patterns\Node {
    return array_pop($this->coverage_trees);
  }

  public static function analyze(Table $spans, Table $types, nodes\Program $prog): void {
    $ctx = new self($spans, $types);

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
    array_push($ctx->match_types, $ctx->types->get($expr->disc->expr));
    $ctx->push_pattern_tree($ctx->types->get($expr->disc->expr));
  }

  private static function match_arm(self $ctx, nodes\MatchArm $arm): void {
    $type    = end($ctx->match_types);
    $pattern = patterns\Pattern::from($arm->pattern, $type);
    if ($ctx->peek_pattern_tree()->is_redundant($pattern)) {
      $span = $ctx->spans->get($arm->pattern);
      throw Errors::redundant_pattern($span, $pattern);
    } else {
      $ctx->peek_pattern_tree()->apply($pattern);
    }
  }

  private static function exit_match_expr(self $ctx, nodes\MatchExpr $expr): void {
    array_pop($ctx->match_types);
    $uncovered = $ctx->pop_pattern_tree()->uncovered_patterns();
    if (!empty($uncovered)) {
      $span = $ctx->spans->get($expr);
      throw Errors::uncovered_patterns($span, $uncovered);
    }
  }
}
