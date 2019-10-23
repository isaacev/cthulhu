<?php

namespace Cthulhu\php;

use Cthulhu\ir;

class Lower {
  private $namespaces = [];
  private $stmt_stack = [];
  private $expr_stack = [];
  private $entry_refs = [];

  // Name resolution variables
  private $ir_name_to_ir_symbol;
  private $ir_symbol_to_ir_name;
  private $ir_symbol_to_type;
  private $ir_expr_to_type;
  private $ir_symbol_to_php_ref;
  private $ir_symbol_to_php_var;
  private $ir_name_to_php_name;
  private $php_symbol_table;
  private $ir_symbol_to_string;
  private $root_scope;
  private $namespace_scopes = [];
  private $namespace_refs = [];
  private $function_scopes = [];
  private $function_heads = [];
  private $block_exit_handlers = [];

  function __construct(
    ir\Table $ir_name_to_ir_symbol,
    ir\Table $ir_symbol_to_ir_name,
    ir\Table $ir_symbol_to_type,
    ir\Table $ir_expr_to_type
  ) {
    $this->ir_name_to_ir_symbol = $ir_name_to_ir_symbol;
    $this->ir_symbol_to_ir_name = $ir_symbol_to_ir_name;
    $this->ir_symbol_to_type = $ir_symbol_to_type;
    $this->ir_expr_to_type = $ir_expr_to_type;
    $this->ir_symbol_to_php_ref = new ir\Table();
    $this->ir_symbol_to_php_var = new ir\Table();
    $this->ir_name_to_php_name = new ir\Table();
    $this->php_symbol_table = new ir\Table();
    $this->ir_symbol_to_string = new ir\Table();
    $this->root_scope = new names\Scope();
  }

  private function push_block(): void {
    array_push($this->stmt_stack, []);
  }

  private function push_stmt(nodes\Stmt $stmt): void {
    $this->stmt_stack[count($this->stmt_stack) - 1][] = $stmt;
  }

  private function pop_block(): nodes\BlockNode {
    return new nodes\BlockNode(array_pop($this->stmt_stack));
  }

  private function push_expr(nodes\Expr $expr): void {
    array_push($this->expr_stack, $expr);
  }

  private function pop_expr(): nodes\Expr {
    return array_pop($this->expr_stack);
  }

  private function pop_exprs(int $n): array {
    $exprs = [];
    while ($n-- > 0) {
      array_unshift($exprs, $this->pop_expr());
    }
    return $exprs;
  }

  private function current_namespace_scope(): ?names\Scope {
    return !empty($this->namespace_scopes)
      ? end($this->namespace_scopes)
      : null;
  }

  private function current_function_scope(): ?names\Scope {
    return !empty($this->function_scopes)
      ? end($this->function_scopes)
      : null;
  }

  private function current_scope(): names\Scope {
    if ($scope = $this->current_function_scope()) {
      return $scope;
    } else if ($scope = $this->current_namespace_scope()) {
      return $scope;
    } else {
      return $this->root_scope;
    }
  }

  private function php_name_from_ir_name(ir\nodes\Name $ir_name): nodes\Name {
    if ($php_name = $this->ir_name_to_php_name->get($ir_name)) {
      return $php_name;
    }

    $ir_symbol = $this->ir_name_to_ir_symbol->get($ir_name);
    $php_value = $this->rename_ir_name($ir_symbol, $ir_name);
    $php_symbol = new names\Symbol();
    $php_name = new nodes\Name($php_value, $php_symbol);
    $this->php_symbol_table->set($php_name, $php_symbol);
    $this->ir_name_to_php_name->set($ir_name, $php_name);
    $php_segments = end($this->namespace_refs)->segments . '\\' . $php_value;
    $php_ref = new nodes\Reference($php_segments, $php_symbol);
    $this->ir_symbol_to_php_ref->set($ir_symbol, $php_ref);
    return $php_name;
  }

  private function php_var_from_ir_name(ir\nodes\Name $ir_name): nodes\Variable {
    $ir_symbol = $this->ir_name_to_ir_symbol->get($ir_name);
    if ($php_var = $this->ir_symbol_to_php_var->get($ir_symbol)) {
      return $php_var;
    }

    $php_value = $this->rename_ir_name($ir_symbol, $ir_name);
    $php_symbol = new names\Symbol();
    $php_var = new nodes\Variable($php_value, $php_symbol);
    $this->php_symbol_table->set($php_var, $php_symbol);
    $this->ir_symbol_to_php_var->set($ir_symbol, $php_var);
    return $php_var;
  }

  private function php_ref_from_ir_name(ir\nodes\Name $ir_name): nodes\Reference {
    $tail_ir_symbol = $this->ir_name_to_ir_symbol->get($ir_name);
    if ($php_ref = $this->ir_symbol_to_php_ref->get($tail_ir_symbol)) {
      return $php_ref;
    }

    $ir_symbol = $tail_ir_symbol;
    $php_values = [ $this->rename_ir_name($ir_symbol, $ir_name) ];
    while ($ir_symbol = $ir_symbol->parent) {
      array_unshift($php_values, $this->ir_symbol_to_string->get($ir_symbol));
    }
    $php_symbol = new names\Symbol();
    $php_ref = new nodes\Reference(implode('\\', $php_values), $php_symbol);
    $this->php_symbol_table->set($php_ref, $php_symbol);
    $this->ir_symbol_to_php_ref->set($tail_ir_symbol, $php_ref);
    return $php_ref;
  }

  private function php_tmp_var(): nodes\Variable {
    $current_scope = $this->current_scope();
    while (true) {
      $candidate = $current_scope->next_tmp_name();
      if ($this->is_name_unavailable($candidate, $current_scope)) {
        continue;
      } else {
        $current_scope->use_name($candidate);
        $php_symbol = new names\Symbol();
        $php_var = new nodes\Variable($candidate, $php_symbol);
        $this->php_symbol_table->set($php_var, $php_symbol);
        return $php_var;
      }
    }
  }

  private function rename_ir_name(ir\names\Symbol $ir_symbol, ir\nodes\Name $ir_name): string {
    $candidate = $ir_name->value;
    $counter = 0;
    $current_scope = $this->current_scope();
    while ($this->is_name_unavailable($candidate, $current_scope)) {
      if ($counter === 0) {
        $candidate = "_$ir_name->value";
      } else {
        $candidate = $ir_name->value . "_$counter";
      }
      $counter++;
    }
    $current_scope->use_name($candidate);
    $this->ir_symbol_to_string->set($ir_symbol, $candidate);
    return $candidate;
  }

  private function is_name_unavailable(string $name, names\Scope $scope): bool {
    return (
      in_array(strtolower($name), names\Reserved::WORDS) ||
      $scope->has_name($name)
    );
  }

  private function push_block_exit_handler(callable $callback): void {
    array_push($this->block_exit_handlers, $callback);
  }

  private function call_block_exit_handler(): void {
    end($this->block_exit_handlers)();
  }

  private function pop_block_exit_handler(): void {
    array_pop($this->block_exit_handlers);
  }

  private function enter_namespace(ir\nodes\Name $ir_name): void {
    array_push($this->namespace_refs, $this->php_ref_from_ir_name($ir_name));
    array_push($this->namespace_scopes, new names\Scope());
  }

  private function exit_namespace(): nodes\Reference {
    array_pop($this->namespace_scopes);
    return array_pop($this->namespace_refs);
  }

  private function enter_function(ir\nodes\FuncHead $ir_head): void {
    $php_name = $this->php_name_from_ir_name($ir_head->name);
    $func_scope = new names\Scope();
    array_push($this->function_scopes, $func_scope);

    // Bind each function parameter to a valid PHP variable name and add to scope
    $params = [];
    foreach ($ir_head->params as $param) {
      $params[] = $this->php_var_from_ir_name($param->name);
    }

    $php_head = new nodes\FuncHead($php_name, $params);
    array_push($this->function_heads, $php_head);
  }

  private function exit_function(): nodes\FuncHead {
    array_pop($this->function_scopes);
    return array_pop($this->function_heads);
  }

  private function native_function(ir\nodes\Name $ir_name, int $num_params): nodes\FuncHead {
    $php_name = $this->php_name_from_ir_name($ir_name);
    $func_scope = new names\Scope();
    array_push($this->function_scopes, $func_scope);

    // Allocate the number of variables as required by the native function
    $php_params = [];
    for ($i = 0; $i < $num_params; $i++) {
      $php_params[] = $this->php_tmp_var();
    }

    array_pop($this->function_scopes);
    return new nodes\FuncHead($php_name, $php_params);
  }

  public static function from(
    ir\Table $ir_name_to_ir_symbol,
    ir\Table $ir_symbol_to_ir_name,
    ir\Table $ir_symbol_to_type,
    ir\Table $ir_expr_to_type,
    ir\nodes\Program $prog
  ): nodes\Program {
    $ctx = new self(
      $ir_name_to_ir_symbol,
      $ir_symbol_to_ir_name,
      $ir_symbol_to_type,
      $ir_expr_to_type
    );

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
      'exit(ReturnStmt)' => function (ir\nodes\ReturnSTmt $stmt) use ($ctx) {
        self::exit_return_stmt($ctx, $stmt);
      },
      'enter(IfExpr)' => function (ir\nodes\IfExpr $expr, ir\Path $path) use ($ctx) {
        self::enter_if_expr($ctx, $expr, $path);
      },
      'exit(IfExpr)' => function (ir\nodes\IfExpr $expr) use ($ctx) {
        self::exit_if_expr($ctx, $expr);
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
      'Block' => function (ir\nodes\Block $block) use ($ctx) {
        self::block($ctx, $block);
      },
    ]);

    return new nodes\Program($ctx->namespaces);
  }

  private static function exit_program(self $ctx): void {
    if (empty($ctx->entry_refs)) {
      throw Errors::no_main_func();
    }

    $ctx->push_block();
    foreach ($ctx->entry_refs as $php_ref) {
      $ctx->push_stmt(
        new nodes\SemiStmt(
          new nodes\CallExpr(
            new nodes\ReferenceExpr($php_ref), [])));
    }
    $block = $ctx->pop_block();
    $ctx->namespaces[] = new nodes\NamespaceNode(null, $block);
  }

  private static function enter_library(self $ctx, ir\nodes\Library $lib): void {
    $ctx->enter_namespace($lib->name);
    $ctx->push_block();
  }

  private static function exit_library(self $ctx): void {
    $block = $ctx->pop_block();
    $php_ref = $ctx->exit_namespace();
    $ctx->namespaces[] = new nodes\NamespaceNode($php_ref, $block);
  }

  private static function enter_mod_item(self $ctx, ir\nodes\ModItem $item): void {
    $ctx->push_block();
    $ctx->enter_namespace($item->name);
  }

  private static function exit_mod_item(self $ctx): void {
    $php_ref = $ctx->exit_namespace();
    $block = $ctx->pop_block();
    $ctx->namespaces[] = new nodes\NamespaceNode($php_ref, $block);
  }

  private static function enter_func_item(self $ctx, ir\nodes\FuncItem $item): void {
    $ctx->enter_function($item->head);

    $ir_symbol = $ctx->ir_name_to_ir_symbol->get($item->head->name);
    $type = $ctx->ir_symbol_to_type->get($ir_symbol);
    $does_return = ir\types\UnitType::does_not_match($type->output);
    if ($does_return) {
      $callback = function () use ($ctx) {
        $expr = $ctx->pop_expr();
        $ctx->push_stmt(new nodes\ReturnStmt($expr));
      };
    } else {
      $callback = function () use ($ctx) {
        $expr = $ctx->pop_expr();
        if (($expr instanceof nodes\NullLiteral) === false) {
          $ctx->push_stmt(new nodes\SemiStmt($expr));
        }
      };
    }
    $ctx->push_block_exit_handler($callback);
  }

  private static function exit_func_item(self $ctx, ir\nodes\FuncItem $item): void {
    $ctx->pop_block_exit_handler();
    $php_head = $ctx->exit_function();
    $php_body = $ctx->pop_block();
    $php_func = new nodes\FuncStmt($php_head, $php_body, $item->attrs);
    $ctx->push_stmt($php_func);

    if ($item->get_attr('entry', false)) {
      $ir_symbol = $ctx->ir_name_to_ir_symbol->get($item->head->name);
      $ctx->entry_refs[] = $ctx->ir_symbol_to_php_ref->get($ir_symbol);
    }
  }

  private static function native_func_item(self $ctx, ir\nodes\NativeFuncItem $item): void {
    $ctx->push_block();
    $php_head = $ctx->native_function($item->name, count($item->note->inputs));

    $args = [];
    foreach ($php_head->params as $param) {
      $args[] = new nodes\VariableExpr($param);
    }

    if ($item->get_attr('construct', false)) {
      $ctx->push_expr(self::builtins($item->name->value, $args));
    } else {
      $ctx->push_expr(
        new nodes\CallExpr(
          new nodes\ReferenceExpr(
            new nodes\Reference($item->name->value, new names\Symbol())),
          $args));
    }

    $ir_symbol = $ctx->ir_name_to_ir_symbol->get($item->name);
    $type = $ctx->ir_symbol_to_type->get($ir_symbol);
    $ctx->push_stmt(ir\types\UnitType::matches($type->output)
      ? new nodes\SemiStmt($ctx->pop_expr())
      : new nodes\ReturnStmt($ctx->pop_expr()));

    $php_body = $ctx->pop_block();
    $php_func = new nodes\FuncStmt($php_head, $php_body, $item->attrs);
    $ctx->push_stmt($php_func);
  }

  private static function exit_let_stmt(self $ctx, ir\nodes\LetStmt $stmt): void {
    $php_var = $ctx->php_var_from_ir_name($stmt->name);
    $php_expr = $ctx->pop_expr();
    $php_stmt = new nodes\AssignStmt($php_var, $php_expr);
    $ctx->push_stmt($php_stmt);
  }

  private static function exit_semi_stmt(self $ctx): void {
    $php_expr = $ctx->pop_expr();
    if (($php_expr instanceof nodes\NullLiteral) === false) {
      $php_stmt = new nodes\SemiStmt($php_expr);
      $ctx->push_stmt($php_stmt);
    }
  }

  private static function exit_return_stmt(self $ctx): void {
    $ctx->call_block_exit_handler();
  }

  private static function enter_if_expr(self $ctx, ir\nodes\IfExpr $expr, ir\Path $path): void {
    $parent_ir_node = $path->parent->node;
    $return_type = $ctx->ir_expr_to_type->get($expr);
    if ($parent_ir_node instanceof ir\nodes\SemiStmt || ir\types\UnitType::matches($return_type)) {
      $ctx->push_expr(new nodes\NullLiteral());
      $ctx->push_block_exit_handler(function () use ($ctx) {
        $php_expr = $ctx->pop_expr();
        $ctx->push_stmt(new nodes\SemiStmt($php_expr));
      });
    } else {
      $php_var = $ctx->php_tmp_var();
      $ctx->push_expr(new nodes\VariableExpr($php_var));
      $ctx->push_block_exit_handler(function () use ($ctx, $php_var) {
        $php_expr = $ctx->pop_expr();
        $ctx->push_stmt(new nodes\AssignStmt($php_var, $php_expr));
      });
    }
  }

  private static function exit_if_expr(self $ctx, ir\nodes\IfExpr $expr): void {
    $ctx->pop_block_exit_handler();
    $else_block = $expr->if_false ? $ctx->pop_block() : null;
    $if_block   = $ctx->pop_block();
    $cond       = $ctx->pop_expr();
    $php_stmt   = new nodes\IfStmt($cond, $if_block, $else_block);
    $ctx->push_stmt($php_stmt);
  }

  private static function exit_call_expr(self $ctx, ir\nodes\CallExpr $expr): void {
    $type   = $ctx->ir_expr_to_type->get($expr->callee);
    $arity  = count($type->inputs);
    $args   = $ctx->pop_exprs($arity);
    $callee = $ctx->pop_expr();
    $expr   = new nodes\CallExpr($callee, $args);
    $ctx->push_expr($expr);
  }

  private static function exit_binary_expr(self $ctx, ir\nodes\BinaryExpr $expr): void {
    $rhs  = $ctx->pop_expr();
    $lhs  = $ctx->pop_expr();
    $op   = self::translate_to_php_binary_operator($expr->op);
    $expr = new nodes\BinaryExpr($op, $lhs, $rhs);
    $ctx->push_expr($expr);
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
    $rhs  = $ctx->pop_expr();
    $op   = self::translate_to_php_unary_operator($expr->op);
    $expr = new nodes\UnaryExpr($op, $rhs);
    $ctx->push_expr($expr);
  }

  private static function translate_to_php_unary_operator(string $op): string {
    switch ($op) {
      default:
        return $op;
    }
  }

  private static function exit_list_expr(self $ctx, ir\nodes\ListExpr $expr): void {
    $exprs = $ctx->pop_exprs(count($expr->elements));
    $expr  = new nodes\OrderedArrayExpr($exprs);
    $ctx->push_expr($expr);
  }

  private static function ref_expr(self $ctx, ir\nodes\RefExpr $expr): void {
    $ir_name = $expr->ref->tail_segment;
    $ir_symbol = $ctx->ir_name_to_ir_symbol->get($ir_name);
    if ($ir_symbol instanceof ir\names\VarSymbol) {
      $php_var  = $ctx->ir_symbol_to_php_var->get($ir_symbol);
      $php_expr = new nodes\VariableExpr($php_var);
    } else {
      $php_ref  = $ctx->ir_symbol_to_php_ref->get($ir_symbol);
      $php_expr = new nodes\ReferenceExpr($php_ref);
    }
    $ctx->push_expr($php_expr);
  }

  private static function str_expr(self $ctx, ir\nodes\StrExpr $expr): void {
    $php_expr = new nodes\StrExpr($expr->value);
    $ctx->push_expr($php_expr);
  }

  private static function int_expr(self $ctx, ir\nodes\IntExpr $expr): void {
    $php_expr = new nodes\IntExpr($expr->value);
    $ctx->push_expr($php_expr);
  }

  private static function bool_expr(self $ctx, ir\nodes\BoolExpr $expr): void {
    $php_expr = new nodes\BoolExpr($expr->value);
    $ctx->push_expr($php_expr);
  }

  private static function block(self $ctx): void {
    $ctx->push_block();
  }

  private static function builtins(string $name, array $args): nodes\Expr {
    switch ($name) {
      case 'subscript':
        return new nodes\SubscriptExpr($args[0], $args[1]);
      case 'print':
        return new nodes\BuiltinCallExpr('print', $args);
      default:
        throw new \Exception("unknown PHP construct: $name");
    }
  }
}
