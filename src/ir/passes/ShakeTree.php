<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes\Closure;
use Cthulhu\ir\nodes\Let;
use Cthulhu\ir\nodes\Module;
use Cthulhu\ir\nodes\NameExpr;
use Cthulhu\ir\nodes\Root;
use Cthulhu\lib\trees\EditablePath;
use Cthulhu\lib\trees\Visitor;

class ShakeTree implements Pass {
  public static function apply(Root $root): Root {
    /* @var int[] $stack */
    $stack = [];

    /* @var int[][] $graph */
    $graph = [];

    /* @var bool[] $reach */
    $reach = [];

    $add_edge = function (int $from, int $to) use (&$graph) {
      if (array_key_exists($from, $graph) === false) {
        $graph[$from] = [ $to ];
      } else {
        $graph[$from][] = $to;
      }
    };

    Visitor::walk($root, [
      'enter(Let)' => function (Let $let) use (&$stack, &$reach) {
        if ($let->expr instanceof Closure && $let->name !== null) {
          array_push($stack, $let->name->symbol->get_id());
        }

        if ($let->name !== null && $let->get('entry') === true) {
          $ref_id         = $let->name->symbol->get_id();
          $reach[$ref_id] = false;
        }
      },
      'exit(Let)' => function (Let $let) use (&$stack) {
        if ($let->expr instanceof Closure && $let->name !== null) {
          array_pop($stack);
        }
      },
      'NameExpr' => function (NameExpr $expr) use (&$stack, &$add_edge) {
        $name    = $expr->name;
        $to_id   = $name->symbol->get_id();
        $from_id = end($stack);
        $add_edge($from_id, $to_id);
      },
    ]);

    $queue = array_keys($reach);
    while ($next_id = array_shift($queue)) {
      if (isset($reach[$next_id]) && $reach[$next_id] === true) {
        continue;
      }

      $reach[$next_id] = true;
      if (array_key_exists($next_id, $graph)) {
        array_push($queue, ...$graph[$next_id]);
      }
    }

    $new_root = Visitor::edit($root, [
      'Let' => function (Let $let, EditablePath $path) use (&$reach) {
        if ($let->expr instanceof Closure && $let->name !== null) {
          $id = $let->name->symbol->get_id();
          if (array_key_exists($id, $reach) === false) {
            $path->remove();
          }
        }
      },
      'exit(Module)' => function (Module $mod, EditablePath $path) {
        if ($mod->stmt === null) {
          $path->remove();
        }
      },
    ]);

    assert($new_root instanceof Root);
    return $new_root;
  }
}
