<?php

namespace Cthulhu\ir;

use Cthulhu\ir\types\hm\Func;
use Cthulhu\ir\types\hm\Type;
use Cthulhu\ir\types\hm\TypeOper;
use Cthulhu\ir\types\hm\TypeVar;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;

class Arity {
  public static function inspect(nodes\Root $root): void {
    Visitor::walk($root, [
      'exit(Stmt|Expr)' => function (nodes\Node $node, Path $path) {
        if (($node->get('arity') instanceof arity\Arity) === false) {
          die("missing arity for $path->kind node\n");
        }
      },
      'Intrinsic' => function (nodes\Intrinsic $intrinsic) {
        $intrinsic->set('arity', new arity\ZeroArity());
      },
      'NameExpr' => function (nodes\NameExpr $expr) {
        $arity = $expr->name->symbol->get('arity');
        $expr->set('arity', $arity);
      },
      'exit(Apply)' => function (nodes\Apply $expr) {
        $callee_arity = $expr->callee->get('arity');
        $total_args   = count($expr->args);
        $arity        = $callee_arity->apply($total_args);
        $expr->set('arity', $arity);
      },
      'Lit' => function (nodes\Lit $lit) {
        $arity = new arity\ZeroArity();
        $lit->set('arity', $arity);
      },
      'enter(Def)' => function (nodes\Def $def) {
        foreach ($def->params->names as $param) {
          $arity = self::type_to_arity($param->type);
          $param->symbol->set('arity', $arity);
        }
      },
      'exit(Def)' => function (nodes\Def $def) {
        $return_arity = ($def->body !== null)
          ? $def->body->last_stmt()->get('arity')
          : new arity\ZeroArity();
        $total_params = max(1, count($def->params));
        $arity        = new arity\KnownMultiArity($total_params, $return_arity);

        $def->set('arity', $arity);
        $def->name->symbol->set('arity', $arity);
      },
      'exit(Let)' => function (nodes\Let $let) {
        $expr_arity = $let->expr->get('arity');
        if ($let->name !== null) {
          $let->name->symbol->set('arity', $expr_arity);
        }

        $stmt_arity = new arity\ZeroArity();
        $let->set('arity', $stmt_arity);
      },
      'exit(Ret)' => function (nodes\Ret $ret) {
        $arity = $ret->expr->get('arity');
        $ret->set('arity', $arity);
      },
    ]);
  }

  private static function type_to_arity(Type $type): arity\Arity {
    $type = self::simplify_type($type);

    if ($type instanceof Func) {
      $output          = self::simplify_type($type->output);
      $output_is_oper  = $output instanceof TypeOper;
      $output_not_func = ($output instanceof Func) === false;
      if ($output_is_oper && $output_not_func) {
        return new arity\KnownMultiArity(1, new arity\UnknownArity());
      }
    }

    return new arity\UnknownArity();
  }

  private static function simplify_type(Type $type): Type {
    if ($type instanceof TypeVar && $type->instance !== null) {
      return self::simplify_type($type->instance);
    } else {
      return $type;
    }
  }
}

