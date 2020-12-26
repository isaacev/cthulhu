<?php

namespace Cthulhu\ir\passes;

use Cthulhu\ir\nodes\Ctor;
use Cthulhu\ir\nodes\Def;
use Cthulhu\ir\nodes\Enum;
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
      'enter(Def)' => function (Def $def) use (&$stack, &$reach) {
        array_push($stack, $def->name->symbol->get_id());
      },
      'exit(Def)' => function () use (&$stack) {
        array_pop($stack);
      },
      'NameExpr' => function (NameExpr $expr) use (&$stack, &$add_edge, &$reach) {
        $name  = $expr->name;
        $to_id = $name->symbol->get_id();

        if (empty($stack)) {
          // If the reference isn't inside of a function body (identified by the
          // empty function reference stack) then the function will always be
          // called so mark the function as reachable in the graph:
          $reach[$to_id] = false;
        } else {
          // If the reference is inside of a function body, create an edge
          // between the parent function and the referenced function to indicate
          // that the referenced function *may* be reachable if the parent
          // function is reachable itself.
          $from_id = end($stack);
          $add_edge($from_id, $to_id);
        }
      },

      'Enum' => function (Enum $enum) use (&$add_edge) {
        $enum_id = $enum->name->symbol->get_id();
        foreach ($enum->forms as $form) {
          $form_id = $form->name->symbol->get_id();
          $add_edge($enum_id, $form_id);
          $add_edge($form_id, $enum_id);
        }
      },
      'Ctor' => function (Ctor $ctor) use (&$stack, &$add_edge, &$reach) {
        $to_id = $ctor->name->symbol->get_id();

        if (empty($stack)) {
          // If the constructor isn't inside of a function body, then the
          // constructor will always be called so mark the constructor as
          // reachable in the graph.
          $reach[$to_id] = false;
        } else {
          // If the reference is inside of a function body, create an edge
          // between the parent function and the referenced constructor to
          // indicate that the constructor *may* be reachable if the parent
          // function is reachable itself.
          $from_id = end($stack);
          $add_edge($from_id, $to_id);
        }
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
      'Def' => function (Def $def, EditablePath $path) use (&$reach) {
        $id = $def->name->symbol->get_id();
        if (array_key_exists($id, $reach) === false) {
          $path->remove();
        }
      },
      'exit(Module)' => function (Module $mod, EditablePath $path) {
        if ($mod->stmt === null) {
          $path->remove();
        }
      },
      'Enum' => function (Enum $enum, EditablePath $path) use (&$reach) {
        $id = $enum->name->symbol->get_id();
        if (array_key_exists($id, $reach) === false) {
          $path->remove();
        }
      },
    ]);

    assert($new_root instanceof Root);
    return $new_root;
  }
}
