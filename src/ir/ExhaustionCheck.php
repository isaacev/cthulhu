<?php

namespace Cthulhu\ir;

use Cthulhu\ast\nodes as ast;
use Cthulhu\lib\trees\Visitor;

class ExhaustionCheck {
  public static function syntax_tree(ast\Program $tree): void {
    /* @var types\Type[] $match_types */
    $match_types = [];

    /* @var patterns\Node[] $match_trees */
    $match_trees = [];

    Visitor::walk($tree, [
      'enter(MatchExpr)' => function (ast\MatchExpr $expr) use (&$match_types, &$match_trees) {
        $match_type = $expr->discriminant->get(TypeCheck::TYPE_KEY);
        array_push($match_types, $match_type);

        $match_tree = patterns\Node::from_type($match_type);
        array_push($match_trees, $match_tree);
      },
      'MatchArm' => function (ast\MatchArm $arm) use (&$match_types, &$match_trees) {
        $match_type = end($match_types);
        $pattern    = patterns\Pattern::from($arm->pattern, $match_type);
        $tree       = end($match_trees);

        if ($tree->is_redundant($pattern)) {
          throw Errors::redundant_pattern($arm->pattern->get('span'), $pattern);
        } else {
          $tree->apply($pattern);
        }
      },
      'exit(MatchExpr)' => function (ast\MatchExpr $expr) use (&$match_types, &$match_trees) {
        array_pop($match_types);
        $uncovered = array_pop($match_trees)->uncovered_patterns();
        if (!empty($uncovered)) {
          $span = $expr->get('span');
          throw Errors::uncovered_patterns($span, $uncovered);
        }
      },
    ]);
  }
}
