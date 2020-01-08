<?php


namespace Cthulhu\php\passes;

use Cthulhu\php;
use Cthulhu\php\visitor;

class NoOp {
  public static function apply(php\nodes\Program $prog): php\nodes\Program {
    $new_prog = visitor\Visitor::edit($prog, [
      'SemiStmt' => function (visitor\Path $path) {
        assert($path->node instanceof php\nodes\SemiStmt);
        if ($path->node->expr instanceof php\nodes\NullLiteral) {
          $path->remove();
        }
      },
    ]);

    assert($new_prog instanceof php\nodes\Program);
    return $new_prog;
  }
}
