<?php

namespace Cthulhu\types;

use Cthulhu\ast;
use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\names\VarSymbol;
use Cthulhu\ir\types\hm;
use Cthulhu\lib\trees\Visitor;

class TypeCompiler {
  private ast\nodes\Program $program;

  /* @var hm\Expr[] $exprs */
  private array $exprs = [];

  /* @var hm\Type[] $types */
  private array $types = [];

  public function __construct(ast\nodes\Program $program) {
    $this->program = $program;
    $this->types   = [
      2 => new hm\Nullary('Bool'),  // FIXME
      3 => new hm\Nullary('Int'),   // FIXME
      4 => new hm\Nullary('Float'), // FIXME
      5 => new hm\Nullary('Str'),   // FIXME
    ];
  }

  private function named_form_constructor(ast\nodes\NamedFormDecl $form, array $new_params, array $mapping): hm\Type {
    $fields = [];
    foreach ($form->params as $pair) {
      $fields[$pair->name->value] = $this->note($pair->note, function (ast\nodes\TypeParamNote $note) use (&$new_params, &$mapping): hm\Type {
        return $new_params[$mapping[$note->get('symbol')->get_id()]];
      });
    }
    return new hm\Record($fields);
  }

  private function ordered_form_constructor(ast\nodes\OrderedFormDecl $form, array $new_params, array $mapping): hm\Type {
    $order = [];
    foreach ($form->params as $note) {
      $order[] = $this->note($note, function (ast\nodes\TypeParamNote $note) use (&$new_params, &$mapping): hm\Type {
        return $new_params[$mapping[$note->get('symbol')->get_id()]];
      });
    }
    return new hm\Tuple($order);
  }

  /**
   * @return hm\Expr[]
   */
  public function exprs(): array {
    Visitor::walk($this->program, [
      'IntrinsicSignature' => function (ast\nodes\IntrinsicSignature $sig): void {
        $this->intrinsic_signature($sig);
      },
      'EnumItem' => function (ast\nodes\EnumItem $item) {
        $params        = [];
        $param_mapping = [];
        foreach ($item->params as $index => $param) {
          $sym_id                 = $param->get('symbol')->get_id();
          $params[]               = $this->types[$sym_id] = new hm\TypeVar();
          $param_mapping[$sym_id] = $index;
        }
        $forms = [];
        foreach ($item->forms as $form) {
          $form_name = $form->name->value;
          if ($form instanceof ast\nodes\NamedFormDecl) {
            $forms[$form_name] = function (array $new_params) use ($form, $param_mapping): hm\Type {
              return self::named_form_constructor($form, $new_params, $param_mapping);
            };
          } else if ($form instanceof ast\nodes\OrderedFormDecl) {
            $forms[$form_name] = function (array $new_params) use ($form, $param_mapping): hm\Type {
              return self::ordered_form_constructor($form, $new_params, $param_mapping);
            };
          } else {
            $forms[$form_name] = function (): hm\Type {
              return new hm\Nullary('Unit');
            };
          }
        }

        $enum_sym = $item->name->get('symbol');
        assert($enum_sym instanceof RefSymbol);
        $enum_type = new hm\Enum("$enum_sym", $params, $forms);
        $enum_sym->set('type', $enum_type);
        $this->types[$enum_sym->get_id()] = $enum_type;
      },
      'exit(FnItem)' => function (ast\nodes\FnItem $item): void {
        if ($item->name instanceof ast\nodes\OperatorRef) {
          $sym = $item->name->oper->get('symbol');
        } else {
          assert($item->name instanceof ast\nodes\LowerName);
          $sym = $item->name->get('symbol');
        }
        assert($sym instanceof Symbol);
        $body = array_pop($this->exprs);
        if (empty($item->params)) {
          $body = new hm\LamExpr(
            new hm\Param(new VarSymbol(), new hm\Nullary('Unit')),
            $body, $this->note($item->returns));
        } else {
          $returns = $this->note($item->returns);
          for ($i = count($item->params) - 1; $i >= 0; $i--) {
            $param = $item->params[$i];
            $body  = new hm\LamExpr(
              new hm\Param(
                $param->name->get('symbol'),
                $this->note($param->note)),
              $body,
              $returns);
            if ($i > 0) {
              $returns = new hm\TypeVar();
            }
          }
        }
        $this->exprs[] = new hm\LetExpr($sym, $body);
      },
      'exit(BlockNode)' => function (ast\nodes\BlockNode $block) {
        $this->exprs[] = new hm\DoExpr(array_splice($this->exprs, -count($block->stmts)));
      },
      'exit(LetStmt)' => function (ast\nodes\LetStmt $stmt) {
        $sym = $stmt->name->get('symbol');
        assert($sym instanceof Symbol);
        $rhs           = array_pop($this->exprs);
        $this->exprs[] = new hm\LetExpr($sym, $rhs);
      },
      'exit(MatchExpr)' => function (ast\nodes\MatchExpr $expr) {
        $handlers     = array_splice($this->exprs, -count($expr->arms));
        $discriminant = array_pop($this->exprs);
        $arms         = [];
        foreach ($expr->arms as $index => $arm) {
          $bindings    = [];
          $new_handler = new hm\DoExpr([
            ...$bindings,
            $handlers[$index],
          ]);
          $arms[]      = new hm\Arm($arm->pattern, $new_handler);
        }
        $this->exprs[] = (new hm\MatchExpr($discriminant, $arms))
          ->set('node', $expr);
      },
      'exit(VariantConstructorExpr)' => function (ast\nodes\VariantConstructorExpr $expr) {
        $enum_sym  = end($expr->path->head)->get('symbol');
        $enum_type = $enum_sym->get('type');
        assert($enum_type instanceof hm\Enum);

        $form_sym  = $expr->path->tail->get('symbol');
        $form_name = $expr->path->tail->value;
        $form_type = $enum_type->get_form($form_name);
        assert($form_type instanceof hm\Type);

        if ($expr->fields instanceof ast\nodes\NamedVariantConstructorFields) {
          $fields      = [];
          $popped_args = array_splice($this->exprs, -count($expr->fields->pairs));
          foreach ($expr->fields->pairs as $index => $pair) {
            $pair_name          = $pair->name->value;
            $pair_expr          = $popped_args[$index];
            $fields[$pair_name] = $pair_expr;
          }
          $args = (new hm\RecordExpr($fields))->set('node', $expr->fields);
        } else if ($expr->fields instanceof ast\nodes\OrderedVariantConstructorFields) {
          $popped_args = array_splice($this->exprs, -count($expr->fields->order));
          $args        = (new hm\TupleExpr($popped_args))->set('node', $expr->fields);
        } else {
          assert($expr->fields === null);
          $args = new hm\UnitExpr();
        }

        $this->exprs[] = (new hm\CtorExpr($enum_sym, $form_sym, $args))
          ->set('node', $expr);
      },
      'exit(BinaryExpr)' => function (ast\nodes\BinaryExpr $expr) {
        $rhs           = array_pop($this->exprs);
        $lhs           = array_pop($this->exprs);
        $op            = (new hm\VarExpr($expr->operator->get('symbol')))->set('node', $expr->operator);
        $this->exprs[] = (new hm\AppExpr(new hm\AppExpr($op, $lhs), $rhs))->set('node', $expr);
      },
      'exit(CallExpr)' => function (ast\nodes\CallExpr $expr) {
        $args   = array_splice($this->exprs, -count($expr->args));
        $callee = array_pop($this->exprs);
        foreach ($args as $arg) {
          $callee = (new hm\AppExpr($callee, $arg))->set('node', $expr);
        }
        $this->exprs[] = $callee;
      },
      'PathExpr' => function (ast\nodes\PathExpr $expr) {
        $this->exprs[] = (new hm\VarExpr($expr->path->tail->get('symbol')))->set('node', $expr);
      },
      'Literal' => function (ast\nodes\Literal $expr) {
        $this->exprs[] = (new hm\LitExpr($expr->value))->set('node', $expr);
      },
    ]);

    return $this->exprs;
  }

  private function intrinsic_signature(ast\nodes\IntrinsicSignature $sig): void {
    $sym = $sig->name->get('symbol');
    assert($sym instanceof Symbol);
    if ($sig->params instanceof ast\nodes\UnitNote) {
      $type = new hm\Func(new hm\Nullary('Unit'), $this->note($sig->returns));
    } else if ($sig->params instanceof ast\nodes\TupleNote) {
      $params  = array_map(fn($t) => $this->note($t), $sig->params->members);
      $returns = $this->note($sig->returns);
      foreach (array_reverse($params) as $param) {
        $returns = new hm\Func($param, $returns);
      }
      $type = $returns;
    } else {
      $type = new hm\Func(
        $this->note($sig->params),
        $this->note($sig->returns));
    }
    $this->exprs[] = new hm\DecExpr($sym, $type);
  }

  private function default_var_handler(ast\nodes\TypeParamNote $note): hm\Type {
    $sym = $note->get('symbol');
    assert($sym instanceof Symbol);
    if (array_key_exists($sym->get_id(), $this->types)) {
      return $this->types[$sym->get_id()];
    } else {
      return $this->types[$sym->get_id()] = new hm\TypeVar();
    }
  }

  private function note(ast\nodes\Note $note, ?callable $var_handler = null): hm\Type {
    switch (true) {
      case $note instanceof ast\nodes\FuncNote:
        return new hm\Func(
          $this->note($note->input, $var_handler),
          $this->note($note->output, $var_handler));
      case $note instanceof ast\nodes\NamedNote:
      {
        $sym = $note->path->tail->get('symbol');
        assert($sym instanceof Symbol);
        if (array_key_exists($sym->get_id(), $this->types)) {
          return $this->types[$sym->get_id()];
        } else {
          die("unknown type: " . $note->path->tail->value . PHP_EOL);
        }
      }
      case $note instanceof ast\nodes\GroupedNote:
        return $this->note($note->inner, $var_handler);
      case $note instanceof ast\nodes\UnitNote:
        return new hm\Nullary('Unit');
      case $note instanceof ast\nodes\TypeParamNote:
        if ($var_handler !== null) {
          return $var_handler($note);
        } else {
          return $this->default_var_handler($note);
        }
      case $note instanceof ast\nodes\ParameterizedNote:
      {
        $sym = $note->inner->path->tail->get('symbol');
        assert($sym instanceof Symbol);
        if (array_key_exists($sym->get_id(), $this->types)) {
          return $this->types[$sym->get_id()];
        } else {
          die("unknown type: " . $note->inner->path->tail->value . PHP_EOL);
        }
      }
      default:
        die('unable to parse note with the type ' . get_class($note) . PHP_EOL);
    }
  }
}
