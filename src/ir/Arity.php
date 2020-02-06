<?php

namespace Cthulhu\ir;

use Cthulhu\ir\types\ConcreteType;
use Cthulhu\ir\types\Func;
use Cthulhu\ir\types\Type;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;

class Arity {
  public static function inspect(nodes\Root $root): void {
    Visitor::walk($root, [
      'exit(Stmt|Expr)' => function (nodes\Node $node, Path $path) {
        if (($node->get('arity') instanceof arity\Arity) === false) {
          if ($node instanceof nodes\NameExpr) {
            echo $node->name->text . PHP_EOL;
          }
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
      'exit(IfExpr)' => function (nodes\IfExpr $expr) {
        $arity = self::type_to_arity($expr->type);
        $expr->set('arity', $arity);
      },
      'exit(Apply)' => function (nodes\Apply $expr) {
        $callee_arity = $expr->callee->get('arity');
        $total_args   = count($expr->args);
        $arity        = $callee_arity->apply($total_args);
        $expr->set('arity', $arity);
      },
      'Lit|ListExpr|Ctor|Tuple|Record|Enum|Block' => function (nodes\Node $node) {
        $arity = new arity\ZeroArity();
        $node->set('arity', $arity);
      },
      'enter(Def)' => function (nodes\Def $def) {
        foreach ($def->params->names as $param) {
          $arity = self::type_to_arity($param->type);
          $param->symbol->set('arity', $arity);
        }

        // When first entering a function definition, produce a minimal arity
        // for the definition, using only the number of parameters and basic
        // information about the return type. By creating a minimal arity now,
        // any recursive calls inside the function can be assigned an arity.
        //
        // If there were no recursive calls in the function body, a more
        // refined arity will be assigned when exiting the function definition
        // using information from the arity of the return expression.
        $return_arity = self::type_to_arity($def->type->output);
        $total_params = max(1, count($def->params));
        $arity        = new arity\KnownMultiArity($total_params, $return_arity);

        $def->set('arity', $arity);
        $def->name->symbol->set('arity', $arity);
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
      'enter(Closure)' => function (nodes\Closure $closure) {
        foreach ($closure->names->names as $param) {
          $arity = self::type_to_arity($param->type);
          $param->symbol->set('arity', $arity);
        }
        $return_arity = self::type_to_arity($closure->func_type->output);
        $total_params = max(1, count($closure->names));
        $arity        = new arity\KnownMultiArity($total_params, $return_arity);
        $closure->set('arity', $arity);
      },
      'exit(Closure)' => function (nodes\Closure $closure) {
        $return_arity = ($closure->stmt !== null)
          ? $closure->stmt->last_stmt()->get('arity')
          : new arity\ZeroArity();
        $total_params = max(1, count($closure->names));
        $arity        = new arity\KnownMultiArity($total_params, $return_arity);
        $closure->set('arity', $arity);
      },
      'VariablePattern' => function (nodes\VariablePattern $pat) {
        $arity = self::type_to_arity($pat->type);
        $pat->name->symbol->set('arity', $arity);
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
      'exit(Match)' => function (nodes\Match $match) {
        $arms = $match->arms->arms;
        /* @var arity\Arity $combined_arity */
        $combined_arity = $arms[0]->handler->expr->get('arity');
        for ($i = 1; $i < count($arms); $i++) {
          $arm_arity      = $arms[$i]->handler->expr->get('arity');
          $combined_arity = $combined_arity->combine($arm_arity);
        }
        $match->set('arity', $combined_arity);
      },
    ]);
  }

  private static function type_to_arity(Type $type): arity\Arity {
    $type = $type->flatten();

    if ($type instanceof Func) {
      $output          = $type->output->flatten();
      $output_is_oper  = $output instanceof ConcreteType;
      $output_not_func = ($output instanceof Func) === false;
      if ($output_is_oper && $output_not_func) {
        return new arity\KnownMultiArity(1, new arity\UnknownArity());
      }
    }

    return new arity\UnknownArity();
  }
}

