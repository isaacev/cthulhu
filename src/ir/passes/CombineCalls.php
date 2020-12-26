<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes\Apply;
use Cthulhu\ir\nodes\Root;
use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;

class CombineCalls implements Pass {
  public static function apply(Root $root): Root {
    $new_root = Visitor::edit($root, [
      'Apply' => function (Apply $app, EditablePath $path) {
        if ($app->callee instanceof Apply) {
          $type   = $app->type;
          $callee = $app->callee->callee;
          $args   = $app->callee->args->append($app->args);
          $path->replace_with(new Apply($type, $callee, $args));
        }
      },
    ]);

    assert($new_root instanceof Root);
    return $new_root;
  }
}
