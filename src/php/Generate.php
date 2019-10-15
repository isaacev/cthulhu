<?php

namespace Cthulhu\php;

use Cthulhu\ir;
use Cthulhu\php;

class Generate {
  private $symbol_to_type;
  private $expr_to_type;
  private $renamer;
  private $namespaces = [];
  private $stmt_stacks = [ [] ];
  private $expr_stack = [];
  private $entry_points = [];
  private $block_exit_handlers = [];

  private function __construct(
    ir\Table $name_to_symbol,
    ir\Table $symbol_to_name,
    ir\Table $symbol_to_type,
    ir\Table $expr_to_type
  ) {
    $this->name_to_symbol = $name_to_symbol;
    $this->symbol_to_type = $symbol_to_type;
    $this->expr_to_type = $expr_to_type;
    $this->renamer = new names\Renamer($name_to_symbol, $symbol_to_name);
  }

  private function add_finished_namespace(php\nodes\NamespaceNode $namespace): void {
    $this->namespaces[] = $namespace;
  }

  private function collect_namespaces(): array {
    $namespaces = $this->namespaces;
    $this->namespaces = [];
    return $namespaces;
  }

  private function add_finished_stmt(php\nodes\Stmt $stmt): void {
    $this->stmt_stacks[count($this->stmt_stacks) - 1][] = $stmt;
  }

  private function push_block(): void {
    array_push($this->stmt_stacks, []);
  }

  private function pop_block(): php\nodes\BlockNode {
    return new php\nodes\BlockNode(array_pop($this->stmt_stacks));
  }

  private function push_expr(php\nodes\Expr $expr): void {
    array_push($this->expr_stack, $expr);
  }

  private function pop_expr(): php\nodes\Expr {
    return array_pop($this->expr_stack);
  }

  private function pop_exprs(int $n): array {
    $exprs = [];
    while ($n-- > 0) {
      array_unshift($exprs, $this->pop_expr());
    }
    return $exprs;
  }

  private function push_block_exit_handler(callable $handler): void {
    array_push($this->block_exit_handlers, $handler);
  }

  private function get_block_exit_handler(): callable {
    return end($this->block_exit_handlers);
  }

  private function pop_block_exit_handler(): void {
    array_pop($this->block_exit_handlers);
  }

  public static function from (
    ir\Table $name_to_symbol,
    ir\Table $symbol_to_name,
    ir\Table $symbol_to_type,
    ir\Table $expr_to_type,
    ir\nodes\Program $prog
  ): php\nodes\Program {
    $ctx = new self($name_to_symbol, $symbol_to_name, $symbol_to_type, $expr_to_type);

    ir\Visitor::walk($prog, [
      'exit(Program)' => function (ir\nodes\Program $prog) use ($ctx) {
        self::exit_program($ctx, $prog);
      },
      'enter(Library)' => function (ir\nodes\Library $lib) use ($ctx) {
        self::enter_library($ctx, $lib);
      },
      'exit(Library)' => function (ir\nodes\Library $lib) use ($ctx) {
        self::exit_library($ctx, $lib);
      },
      'enter(ModItem)' => function (ir\nodes\ModItem $item) use ($ctx) {
        self::enter_mod_item($ctx, $item);
      },
      'exit(ModItem)' => function (ir\nodes\ModItem $item) use ($ctx) {
        self::exit_mod_item($ctx, $item);
      },
      'enter(FuncItem)' => function (ir\nodes\FuncItem $item) use ($ctx) {
        self::enter_func_item($ctx, $item);
      },
      'exit(FuncItem)' => function (ir\nodes\FuncItem $item) use ($ctx) {
        self::exit_func_item($ctx, $item);
      },
      'NativeFuncItem' => function (ir\nodes\NativeFuncItem $item) use ($ctx) {
        self::native_func_item($ctx, $item);
      },
      'exit(LetStmt)' => function (ir\nodes\LetStmt $stmt) use ($ctx) {
        self::exit_let_stmt($ctx, $stmt);
      },
      'exit(SemiStmt)' => function (ir\nodes\SemiStmt $stmt) use ($ctx) {
        self::exit_semi_stmt($ctx, $stmt);
      },
      'exit(ReturnStmt)' => function (ir\nodes\ReturnStmt $stmt) use ($ctx) {
        self::exit_return_stmt($ctx, $stmt);
      },
      'enter(IfExpr)' => function (ir\nodes\IfExpr $expr, ir\Path $path) use ($ctx) {
        self::enter_if_expr($ctx, $expr, $path);
      },
      'exit(IfExpr)' => function (ir\nodes\IfExpr $expr, ir\Path $path) use ($ctx) {
        self::exit_if_expr($ctx, $expr, $path);
      },
      'exit(CallExpr)' => function (ir\nodes\CallExpr $expr) use ($ctx) {
        self::exit_call_expr($ctx, $expr);
      },
      'exit(BinaryExpr)' => function (ir\nodes\BinaryExpr $expr) use ($ctx) {
        self::exit_binary_expr($ctx, $expr);
      },
      'exit(UnaryExpr)' => function (ir\nodes\UnaryExpr $expr) use ($ctx) {
        self::exit_unary_expr($ctx, $expr);
      },
      'exit(ListExpr)' => function (ir\nodes\ListExpr $expr) use ($ctx) {
        self::exit_list_expr($ctx, $expr);
      },
      'RefExpr' => function (ir\nodes\RefExpr $expr) use ($ctx) {
        self::ref_expr($ctx, $expr);
      },
      'StrExpr' => function (ir\nodes\StrExpr $expr) use ($ctx) {
        self::str_expr($ctx, $expr);
      },
      'IntExpr' => function (ir\nodes\IntExpr $expr) use ($ctx) {
        self::int_expr($ctx, $expr);
      },
      'BoolExpr' => function (ir\nodes\BoolExpr $expr) use ($ctx) {
        self::bool_expr($ctx, $expr);
      },
      'Block' => function (ir\nodes\Block $expr) use ($ctx) {
        self::block_expr($ctx, $expr);
      },
    ]);

    return new php\nodes\Program($ctx->collect_namespaces());
  }

  private static function exit_program(self $ctx): void {
    if (empty($ctx->entry_points)) {
      throw Errors::no_main_func();
    }

    $ctx->push_block();
    foreach ($ctx->entry_points as $ref_expr) {
      $ctx->add_finished_stmt(new php\nodes\SemiStmt(
        new php\nodes\CallExpr($ref_expr, [])));
    }
    $stmts = $ctx->pop_block();
    $ctx->add_finished_namespace(new php\nodes\NamespaceNode(null, $stmts));
  }

  private static function enter_library(self $ctx, ir\nodes\Library $lib): void {
    $ctx->push_block();
    $ctx->renamer->enter_namespace($lib->name);
  }

  private static function exit_library(self $ctx): void {
    $ref = $ctx->renamer->exit_namespace();
    $stmts = $ctx->pop_block();
    $ctx->add_finished_namespace(new php\nodes\NamespaceNode($ref, $stmts));
  }

  private static function enter_mod_item(self $ctx, ir\nodes\ModItem $item): void {
    $ctx->push_block();
    $ctx->renamer->enter_namespace($item->name);
  }

  private static function exit_mod_item(self $ctx): void {
    $ref = $ctx->renamer->exit_namespace();
    $stmts = $ctx->pop_block();
    $ctx->add_finished_namespace(new php\nodes\NamespaceNode($ref, $stmts));
  }

  private static function enter_func_item(self $ctx, ir\nodes\FuncItem $item): void {
    $type = $ctx->symbol_to_type->get($ctx->name_to_symbol->get($item->name));
    $does_return = ir\types\UnitType::does_not_match($type->output);
    $ctx->renamer->enter_function($item->name, $item->params);
    $ctx->push_block_exit_handler(function () use ($ctx, $does_return) {
      if ($does_return) {
        $expr = $ctx->pop_expr();
        $ctx->add_finished_stmt(new php\nodes\ReturnStmt($expr));
      } else {
        $expr = $ctx->pop_expr();
        if (($expr instanceof php\nodes\NullLiteral) === false) {
          $ctx->add_finished_stmt(new php\nodes\SemiStmt($expr));
        }
      }
    });
  }

  private static function exit_func_item(self $ctx, ir\nodes\FuncItem $item): void {
    $ctx->pop_block_exit_handler();
    list($name, $params) = $ctx->renamer->exit_function();
    $body = $ctx->pop_block();
    $stmt = new php\nodes\FuncStmt($name, $params, $body, $item->attrs);
    $ctx->add_finished_stmt($stmt);

    if ($item->get_attr('entry', false)) {
      $ctx->entry_points[] = $ctx->renamer->name_to_ref_expr($item->name);
    }
  }

  private static function native_func_item(self $ctx, ir\nodes\NativeFuncItem $item): void {
    $ctx->push_block();
    list($name, $params) = $ctx->renamer->native_function($item->name, count($item->note->inputs));

    $args = array_map(function ($param) {
      return new php\nodes\VariableExpr($param);
    }, $params);

    if ($item->get_attr('construct', false)) {
      $ctx->push_expr(self::builtins($item->name->value, $args));
    } else {
      $ctx->push_expr(
        new php\nodes\CallExpr(
          new php\nodes\ReferenceExpr(
            new php\nodes\Reference([ $item->name->value ])),
          $args));
    }

    $symbol = $ctx->name_to_symbol->get($item->name);
    $type = $ctx->symbol_to_type->get($symbol);
    $ctx->add_finished_stmt(ir\types\UnitType::matches($type->output)
      ? new php\nodes\SemiStmt($ctx->pop_expr())
      : new php\nodes\ReturnSTmt($ctx->pop_expr()));

    $body = $ctx->pop_block();
    $stmt = new php\nodes\FuncStmt($name, $params, $body, $item->attrs);
    $ctx->add_finished_stmt($stmt);
  }

  private static function exit_let_stmt(self $ctx, ir\nodes\LetStmt $stmt): void {
    $var = $ctx->renamer->var_from_name($stmt->name);
    $expr = $ctx->pop_expr();
    $stmt = new php\nodes\AssignStmt($var, $expr);
    $ctx->add_finished_stmt($stmt);
  }

  private static function exit_semi_stmt(self $ctx, ir\nodes\SemiStmt $stmt): void {
    $expr = $ctx->pop_expr();
    if (($expr instanceof php\nodes\NullLiteral) === false) {
      $stmt = new php\nodes\SemiStmt($expr);
      $ctx->add_finished_stmt($stmt);
    }
  }

  private static function exit_return_stmt(self $ctx): void {
    $ctx->get_block_exit_handler()();
  }

  private static function enter_if_expr(self $ctx, ir\nodes\IfExpr $expr, ir\Path $path): void {
    $parent_node = $path->parent->node;
    $return_type = $ctx->expr_to_type->get($expr);
    if ($parent_node instanceof ir\nodes\SemiStmt || ir\types\UnitType::matches($return_type)) {
      $ctx->push_expr(new php\nodes\NullLiteral());
      $ctx->push_block_exit_handler(function () use ($ctx) {
        $expr = $ctx->pop_expr();
        $ctx->add_finished_stmt(new php\nodes\SemiStmt($expr));
      });
    } else {
      $var = $ctx->renamer->tmp_var();
      $ctx->push_expr(new php\nodes\VariableExpr($var));
      $ctx->push_block_exit_handler(function () use ($ctx, $var) {
        $expr = $ctx->pop_expr();
        $ctx->add_finished_stmt(new php\nodes\AssignStmt($var, $expr));
      });
    }
  }

  private static function exit_if_expr(self $ctx, ir\nodes\IfExpr $expr, ir\Path $path): void {
    $ctx->pop_block_exit_handler();

    $else_block = $expr->if_false ? $ctx->pop_block() : null;
    $if_block = $ctx->pop_block();
    $cond = $ctx->pop_expr();
    $stmt = new php\nodes\IfStmt($cond, $if_block, $else_block);
    $ctx->add_finished_stmt($stmt);
  }

  private static function exit_call_expr(self $ctx, ir\nodes\CallExpr $expr): void {
    $type   = $ctx->expr_to_type->get($expr->callee);
    $arity  = count($type->inputs);
    $args   = $ctx->pop_exprs($arity);
    $callee = $ctx->pop_expr();
    $ctx->push_expr(new php\nodes\CallExpr($callee, $args));
  }

  private static function exit_binary_expr(self $ctx, ir\nodes\BinaryExpr $expr): void {
    $rhs = $ctx->pop_expr();
    $lhs = $ctx->pop_expr();
    $op  = self::translate_to_php_binary_operator($expr->op);
    $ctx->push_expr(new php\nodes\BinaryExpr($op, $lhs, $rhs));
  }

  private static function translate_to_php_binary_operator(string $op): string {
    switch ($op) {
      case '++':
        return '.';
      default:
        return $op;
    }
  }

  private static function exit_unary_expr(self $ctx, ir\nodes\UnaryExpr $expr): void {
    $rhs = $ctx->pop_expr();
    $op  = self::translate_to_php_unary_operator($expr->op);
    $ctx->push_expr(new php\nodes\UnaryExpr($op, $rhs));
  }

  private static function translate_to_php_unary_operator(string $op): string {
    switch ($op) {
      default:
        return $op;
    }
  }

  private static function exit_list_expr(self $ctx, ir\nodes\ListExpr $expr): void {
    $elements = $ctx->pop_exprs(count($expr->elements));
    $ctx->push_expr(new nodes\FlatArrayExpr($elements));
  }

  private static function ref_expr(self $ctx, ir\nodes\RefExpr $expr): void {
    $ctx->push_expr($ctx->renamer->resolve_ref_expr($expr));
  }

  private static function str_expr(self $ctx, ir\nodes\StrExpr $expr): void {
    $ctx->push_expr(new php\nodes\StrExpr($expr->value));
  }

  private static function int_expr(self $ctx, ir\nodes\IntExpr $expr): void {
    $ctx->push_expr(new php\nodes\IntExpr($expr->value));
  }

  private static function bool_expr(self $ctx, ir\nodes\BoolExpr $expr): void {
    $ctx->push_expr(new php\nodes\BoolExpr($expr->value));
  }

  private static function block_expr(self $ctx): void {
    $ctx->push_block();
  }

  private static function builtins(string $name, array $args): php\nodes\Expr {
    switch ($name) {
      case 'subscript':
        return new php\nodes\SubscriptExpr($args[0], $args[1]);
      case 'print':
        return new php\nodes\BuiltinCallExpr('print', $args);
      default:
        throw new \Exception("unknown PHP construct: $name");
    }
  }
}
