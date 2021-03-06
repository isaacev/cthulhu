<?php

namespace Cthulhu\ir;

use Cthulhu\ir\types\ConcreteType;
use Cthulhu\ir\types\Func;
use Cthulhu\ir\types\Type;
use Cthulhu\lib\panic\Panic;
use Cthulhu\lib\trees\Path;
use Cthulhu\lib\trees\Visitor;

class Arity {
  public static function inspect(nodes\Root $root): void {
    Visitor::walk($root, [
      // A shallow pass over all Def nodes is done first to accommodate
      // out-of-order function application.
      'enter(Def)' => function (nodes\Def $def, Path $path) {
        foreach ($def->params->names as $param) {
          $arity = self::type_to_arity($param->type);
          assert($arity instanceof arity\Arity);
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

        $path->abort_recursion();
      },
    ]);

    Visitor::walk($root, [
      'exit(Stmt|Expr)' => function (nodes\Node $node, Path $path) {
        if (($node->get('arity') instanceof arity\Arity) === false) {
          Panic::with_reason(__LINE__, __FILE__, "missing arity for $path->kind node");
        }
      },
      'Intrinsic' => function (nodes\Intrinsic $intrinsic) {
        $intrinsic->set('arity', new arity\ZeroArity());
      },
      'NameExpr' => function (nodes\NameExpr $expr) {
        $arity = $expr->name->symbol->get('arity');
        assert($arity instanceof arity\Arity);
        $expr->set('arity', $arity);
      },
      'exit(Lookup)' => function (nodes\Lookup $expr) {
        $expr->set('arity', self::type_to_arity($expr->type));
      },
      'exit(IfExpr)' => function (nodes\IfExpr $expr) {
        $consequent_stmt = $expr->consequent->first ? $expr->consequent->first->last_stmt() : null;
        $alternate_stmt  = $expr->consequent->first ? $expr->consequent->first->last_stmt() : null;

        $fallback_arity   = self::type_to_arity($expr->type);
        $consequent_arity = ($consequent_stmt ? $consequent_stmt->get('arity') : null) ?? $fallback_arity;
        $alternate_arity  = ($alternate_stmt ? $alternate_stmt->get('arity') : null) ?? $fallback_arity;
        $expr_arity       = $consequent_arity->combine($alternate_arity);
        $expr->set('arity', $expr_arity);
      },
      'exit(Apply)' => function (nodes\Apply $expr) {
        $callee_arity = $expr->callee->get('arity');
        $total_args   = count($expr->args);
        $arity        = $callee_arity->apply($total_args);
        assert($arity instanceof arity\Arity);
        $expr->set('arity', $arity);
      },
      'Lit|ListExpr|Ctor|Tuple|Record|Enum|Block|Pop|Unreachable' => function (nodes\Node $node) {
        $arity = new arity\ZeroArity();
        $node->set('arity', $arity);
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
        assert($arity instanceof arity\Arity);
        $pat->name->symbol->set('arity', $arity);
      },
      'exit(Let)' => function (nodes\Let $let) {
        $expr_arity = $let->expr->get('arity');
        $let->name->symbol->set('arity', $expr_arity);
        $stmt_arity = new arity\ZeroArity();
        $let->set('arity', $stmt_arity);
      },
      'exit(Ret)' => function (nodes\Ret $ret) {
        $arity = $ret->expr->get('arity');
        assert($arity instanceof arity\Arity);
        $ret->set('arity', $arity);
      },
      'exit(MatchExpr)' => function (nodes\MatchExpr $match) {
        $arms = $match->arms->arms;
        /* @var arity\Arity $combined_arity */
        $combined_arity = $arms[0]->handler->stmt->get('arity');
        for ($i = 1; $i < count($arms); $i++) {
          $arm_arity      = $arms[$i]->handler->stmt->get('arity');
          $combined_arity = $combined_arity->combine($arm_arity);
        }
        assert($combined_arity instanceof arity\Arity);
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

