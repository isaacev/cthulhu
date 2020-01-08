<?php

namespace Cthulhu\php;

use Cthulhu\err\Error;
use Cthulhu\ir;
use Cthulhu\val\BooleanValue;
use Cthulhu\val\IntegerValue;
use Cthulhu\val\StringValue;

class Lower {
  private array $namespaces = [];
  private array $stmt_stack = [];
  private array $expr_stack = [];
  private array $entry_refs = [];
  private array $match_out_vars = [];
  private array $match_in_vars = [];
  private array $match_arms = [];
  private array $match_tests = [];

  // Name resolution variables
  private names\Scope $root_scope;
  private ?nodes\NamespaceNode $helper_namespace = null;
  private array $helper_refs = [];
  private array $namespace_scopes = [];
  private array $namespace_refs = [];
  private array $function_scopes = [];
  private array $function_heads = [];
  private array $block_exit_handlers = [];

  public function __construct() {
    $this->root_scope = new names\Scope();
    $this->root_scope->use_name('runtime');
  }

  private function reference_helper(string $helper_name): nodes\Reference {
    if ($this->helper_namespace === null) {
      $this->helper_namespace = new nodes\NamespaceNode(
        new nodes\Reference('runtime', new names\Symbol()),
        new nodes\BlockNode([]));
      array_unshift($this->namespaces, $this->helper_namespace);
    }

    if (array_key_exists($helper_name, $this->helper_refs) === false) {
      $stmt     = Helpers::get($helper_name, $this->helper_namespace->name);
      $symbol   = $stmt->head->name->symbol;
      $segments = $this->helper_namespace->name->segments . '\\' . $stmt->head->name->value;
      $ref      = new nodes\Reference($segments, $symbol);

      $this->helper_namespace->block->stmts[] = $stmt;
      return $this->helper_refs[$helper_name] = $ref;
    }

    return $this->helper_refs[$helper_name];
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

  /**
   * @param int $n
   * @return nodes\Expr[]
   */
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
    if ($php_name = $ir_name->get('php/name')) {
      return $php_name;
    }

    $ir_symbol  = $ir_name->get('symbol');
    $php_value  = $this->rename_ir_name($ir_symbol, $ir_name);
    $php_symbol = new names\Symbol();
    $php_name   = new nodes\Name($php_value, $php_symbol);
    $ir_name->set('php/name', $php_name);
    $php_segments = end($this->namespace_refs)->segments . '\\' . $php_value;
    $php_ref      = new nodes\Reference($php_segments, $php_symbol);
    $ir_symbol->set('php/ref', $php_ref);
    return $php_name;
  }

  private function php_var_from_ir_name(ir\nodes\Name $ir_name): nodes\Variable {
    $ir_symbol = $ir_name->get('symbol');
    if ($php_var = $ir_symbol->get('php/var')) {
      return $php_var;
    }

    $php_value  = $this->rename_ir_name($ir_symbol, $ir_name);
    $php_symbol = new names\Symbol();
    $php_var    = new nodes\Variable($php_value, $php_symbol);
    $ir_symbol->set('php/var', $php_var);
    return $php_var;
  }

  private function php_ref_from_ir_name(ir\nodes\Name $ir_name): nodes\Reference {
    $tail_ir_symbol = $ir_name->get('symbol');
    if ($php_ref = $tail_ir_symbol->get('php/ref')) {
      return $php_ref;
    }

    $ir_symbol  = $tail_ir_symbol;
    $php_values = [ $this->rename_ir_name($ir_symbol, $ir_name) ];
    while ($ir_symbol = $ir_symbol->parent) {
      assert($ir_symbol instanceof ir\names\Symbol);
      array_unshift($php_values, $ir_symbol->get('php/string'));
    }
    $php_symbol = new names\Symbol();
    $php_ref    = new nodes\Reference(implode('\\', $php_values), $php_symbol);
    $tail_ir_symbol->set('php/ref', $php_ref);
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
        return new nodes\Variable($candidate, $php_symbol);
      }
    }
    die('unreachable');
  }

  private function php_var_from_string(string $basis): nodes\Variable {
    $current_scope = $this->current_scope();
    $counter       = 0;
    while (true) {
      $candidate = $counter === 0 ? $basis : "${basis}_$counter";
      if ($this->is_name_unavailable($candidate, $current_scope)) {
        $counter++;
        continue;
      } else {
        $current_scope->use_name($candidate);
        $php_symbol = new names\Symbol();
        return new nodes\Variable($candidate, $php_symbol);
      }
    }
    die('unreachable');
  }

  private function rename_ir_name(ir\names\Symbol $ir_symbol, ir\nodes\Name $ir_name): string {
    $candidate     = $ir_name->value;
    $counter       = 0;
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
    $ir_symbol->set('php/string', $candidate);
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

  private function enter_method(): void {
    array_push($this->function_scopes, new names\Scope());
    $this->push_block();
  }

  private function exit_method(): nodes\BlockNode {
    $block = $this->pop_block();
    array_pop($this->function_scopes);
    return $block;
  }

  private function enter_function(ir\nodes\FuncHead $ir_head): void {
    $php_name   = $this->php_name_from_ir_name($ir_head->name);
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
    $php_name   = $this->php_name_from_ir_name($ir_name);
    $func_scope = new names\Scope();
    array_push($this->function_scopes, $func_scope);

    // Allocate the number of variables as required by the native function
    $php_params = [];
    for ($i = 0; $i < $num_params; $i++) {
      $php_params[] = $this->php_tmp_var();
    }

    return new nodes\FuncHead($php_name, $php_params);
  }

  /**
   * @param ir\nodes\Program $prog
   * @return nodes\Program
   * @throws Error
   * @noinspection PhpDocRedundantThrowsInspection
   */
  public static function from(ir\nodes\Program $prog): nodes\Program {
    $ctx = new self();

    ir\Visitor::walk($prog, [
      'exit(Program)' => function () use ($ctx) {
        self::exit_program($ctx);
      },
      'enter(Library)' => function (ir\nodes\Library $lib) use ($ctx) {
        self::enter_library($ctx, $lib);
      },
      'exit(Library)' => function () use ($ctx) {
        self::exit_library($ctx);
      },
      'enter(ModItem)' => function (ir\nodes\ModItem $item) use ($ctx) {
        self::enter_mod_item($ctx, $item);
      },
      'exit(ModItem)' => function () use ($ctx) {
        self::exit_mod_item($ctx);
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
      'UnionItem' => function (ir\nodes\UnionItem $item) use ($ctx) {
        self::union_item($ctx, $item);
      },
      'exit(LetStmt)' => function (ir\nodes\LetStmt $stmt) use ($ctx) {
        self::exit_let_stmt($ctx, $stmt);
      },
      'exit(SemiStmt)' => function () use ($ctx) {
        self::exit_semi_stmt($ctx);
      },
      'exit(ReturnStmt)' => function () use ($ctx) {
        self::exit_return_stmt($ctx);
      },
      'enter(MatchExpr)' => function (ir\nodes\MatchExpr $expr, ir\Path $path) use ($ctx) {
        self::enter_match_expr($ctx, $expr, $path);
      },
      'exit(MatchExpr)' => function () use ($ctx) {
        self::exit_match_expr($ctx);
      },
      'exit(MatchDiscriminant)' => function () use ($ctx) {
        self::exit_match_discriminant($ctx);
      },
      'enter(MatchArm)' => function (ir\nodes\MatchArm $node) use ($ctx) {
        self::enter_match_arm($ctx, $node);
      },
      'exit(MatchArm)' => function () use ($ctx) {
        self::exit_match_arm($ctx);
      },
      'enter(MatchHandler)' => function (ir\nodes\MatchHandler $node) use ($ctx) {
        self::enter_match_handler($ctx, $node);
      },
      'exit(MatchHandler)' => function (ir\nodes\MatchHandler $node) use ($ctx) {
        self::exit_match_handler($ctx, $node);
      },
      'enter(IfExpr)' => function (ir\nodes\IfExpr $expr, ir\Path $path) use ($ctx) {
        self::enter_if_expr($ctx, $expr, $path);
      },
      'exit(IfExpr)' => function (ir\nodes\IfExpr $expr) use ($ctx) {
        self::exit_if_expr($ctx, $expr);
      },
      'exit(CallExpr)' => function (ir\nodes\CallExpr $expr, ir\Path $path) use ($ctx) {
        self::exit_call_expr($ctx, $expr, $path);
      },
      'exit(PipeExpr)' => function (ir\nodes\PipeExpr $expr) use ($ctx) {
        self::exit_pipe_expr($ctx, $expr);
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
      'exit(VariantConstructorExpr)' => function (ir\nodes\VariantConstructorExpr $expr) use ($ctx) {
        self::exit_variant_constructor_expr($ctx, $expr);
      },
      'RefExpr' => function (ir\nodes\RefExpr $expr, ir\Path $path) use ($ctx) {
        self::ref_expr($ctx, $expr, $path);
      },
      'StrLiteral' => function (ir\nodes\StrLiteral $expr) use ($ctx) {
        self::str_literal($ctx, $expr);
      },
      'FloatLiteral' => function (ir\nodes\FloatLiteral $expr) use ($ctx) {
        self::float_literal($ctx, $expr);
      },
      'IntLiteral' => function (ir\nodes\IntLiteral $expr) use ($ctx) {
        self::int_literal($ctx, $expr);
      },
      'BoolLiteral' => function (ir\nodes\BoolLiteral $expr) use ($ctx) {
        self::bool_literal($ctx, $expr);
      },
      'UnitLiteral' => function () use ($ctx) {
        self::unit_literal($ctx);
      },
      'Block' => function () use ($ctx) {
        self::block($ctx);
      },
    ]);

    return new nodes\Program($ctx->namespaces);
  }

  /**
   * @param Lower $ctx
   * @throws Error
   */
  private static function exit_program(self $ctx): void {
    if (empty($ctx->entry_refs)) {
      throw Errors::no_main_func();
    }

    $ctx->push_block();
    foreach ($ctx->entry_refs as $php_ref) {
      $ctx->push_stmt(
        new nodes\SemiStmt(
          new nodes\CallExpr(
            new nodes\ReferenceExpr($php_ref, false), [])));
    }
    $block             = $ctx->pop_block();
    $ctx->namespaces[] = new nodes\NamespaceNode(null, $block);
  }

  private static function enter_library(self $ctx, ir\nodes\Library $lib): void {
    $ctx->enter_namespace($lib->name);
    $ctx->push_block();
  }

  private static function exit_library(self $ctx): void {
    $block             = $ctx->pop_block();
    $php_ref           = $ctx->exit_namespace();
    $ctx->namespaces[] = new nodes\NamespaceNode($php_ref, $block);
  }

  private static function enter_mod_item(self $ctx, ir\nodes\ModItem $item): void {
    $ctx->push_block();
    $ctx->enter_namespace($item->name);
  }

  private static function exit_mod_item(self $ctx): void {
    $php_ref           = $ctx->exit_namespace();
    $block             = $ctx->pop_block();
    $ctx->namespaces[] = new nodes\NamespaceNode($php_ref, $block);
  }

  private static function enter_func_item(self $ctx, ir\nodes\FuncItem $item): void {
    $ctx->enter_function($item->head);

    $ir_symbol   = $item->head->name->get('symbol');
    $type        = $ir_symbol->get('type');
    $does_return = ir\types\UnitType::does_not_match($type->output);
    if ($does_return) {
      $callback = function () use ($ctx) {
        $expr = $ctx->pop_expr();
        if (($expr instanceof nodes\NullLiteral) === false) {
          $ctx->push_stmt(new nodes\ReturnStmt($expr));
        }
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
      $ir_symbol         = $item->head->name->get('symbol');
      $ctx->entry_refs[] = $ir_symbol->get('php/ref');
    }
  }

  private static function native_func_item(self $ctx, ir\nodes\NativeFuncItem $item): void {
    $ctx->push_block();
    $php_head = $ctx->native_function($item->name, count($item->note->inputs));

    $args = [];
    foreach ($php_head->params as $param) {
      $args[] = new nodes\VariableExpr($param);
    }

    if ($item->get_attr('intrinsic', false)) {
      switch ($item->name->value) {
        default:
          die("no intrinsic named '$item->name'\n");
      }
    } else {
      if ($item->get_attr('construct', false)) {
        $ctx->push_expr(self::builtins($item->name->value, $args));
      } else {
        $ctx->push_expr(
          new nodes\CallExpr(
            new nodes\ReferenceExpr(
              new nodes\Reference($item->name->value, new names\Symbol()),
              false),
            $args));
      }

      $type = $item->name->get('symbol')->get('type');
      $ctx->push_stmt(ir\types\UnitType::matches($type->output)
        ? new nodes\SemiStmt($ctx->pop_expr())
        : new nodes\ReturnStmt($ctx->pop_expr()));
    }

    $php_body = $ctx->pop_block();
    array_pop($ctx->function_scopes);
    $php_func = new nodes\FuncStmt($php_head, $php_body, $item->attrs);
    $ctx->push_stmt($php_func);
  }

  private static function union_item(self $ctx, ir\nodes\UnionItem $item): void {
    $php_base_name = $ctx->php_name_from_ir_name($item->name);
    $php_base_ref  = $ctx->php_ref_from_ir_name($item->name);
    $php_base      = new nodes\ClassStmt(true, $php_base_name, null, []);
    $ctx->push_stmt($php_base);

    foreach ($item->variants as $name => $variant) {
      $php_variant_name = $ctx->php_name_from_ir_name($variant->name);
      switch (true) {
        case $variant instanceof ir\nodes\NamedVariantDeclNode:
        {
          $body = [];
          $ctx->enter_method();
          $params = [ $ctx->php_var_from_string('args') ];
          foreach ($variant->fields as $field) {
            $php_var = $ctx->php_var_from_ir_name($field->name);
            $body[]  = new nodes\PropertyNode(true, $php_var);
            $ctx->push_stmt(new nodes\AssignStmt(
              new nodes\PropertyAccessExpr(
                new nodes\ThisExpr(),
                $php_var),
              new nodes\SubscriptExpr(
                new nodes\VariableExpr($params[0]),
                new nodes\StrLiteral(
                  StringValue::from_safe_scalar($php_var->value)))));
          }
          $body[] = new nodes\MagicMethodNode('__construct', $params, $ctx->exit_method());
          break;
        }
        case $variant instanceof ir\nodes\OrderedVariantDeclNode:
        {
          $body = [];
          $ctx->enter_method();
          $params = [];
          for ($i = 0; $i < count($variant->members); $i++) {
            $php_var = $params[] = $ctx->php_tmp_var();
            $ctx->push_stmt(
              new nodes\AssignStmt(
                new nodes\DynamicPropertyAccessExpr(
                  new nodes\ThisExpr(),
                  new nodes\IntLiteral(
                    IntegerValue::from_scalar($i))),
                new nodes\VariableExpr($php_var)
              )
            );
          }
          $body[] = new nodes\MagicMethodNode('__construct', $params, $ctx->exit_method());
          break;
        }
        default:
          $body = [];
      }

      $php_variant = new nodes\ClassStmt(false, $php_variant_name, $php_base_ref, $body);
      $ctx->push_stmt($php_variant);
    }
  }

  private static function exit_let_stmt(self $ctx, ir\nodes\LetStmt $stmt): void {
    $php_var  = $ctx->php_var_from_ir_name($stmt->name);
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

  private static function enter_match_expr(self $ctx, ir\nodes\MatchExpr $expr, ir\Path $path): void {
    // This is the variable that all match arms will assign values to.
    $php_var               = $ctx->php_tmp_var();
    $ctx->match_out_vars[] = $php_var;
    $ctx->match_arms[]     = [];

    $parent_ir_node = $path->parent->node;
    $return_type    = $expr->get('type');
    if ($parent_ir_node instanceof ir\nodes\SemiStmt || ir\types\UnitType::matches($return_type)) {
      $ctx->push_expr(new nodes\NullLiteral());
      $ctx->push_block_exit_handler(function () use ($ctx) {
        $php_expr = $ctx->pop_expr();
        $ctx->push_stmt(new nodes\SemiStmt($php_expr));
      });
    } else {
      $ctx->push_expr(new nodes\VariableExpr($php_var));
      $ctx->push_block_exit_handler(function () use ($ctx, $php_var) {
        $php_expr = $ctx->pop_expr();
        $ctx->push_stmt(new nodes\AssignStmt($php_var, $php_expr));
      });
    }
  }

  private static function exit_match_expr(self $ctx): void {
    $ctx->pop_block_exit_handler();
    array_pop($ctx->match_in_vars);
    $arms = array_pop($ctx->match_arms);
    assert(!empty($arms));
    $php_stmt = null;
    for ($i = count($arms) - 1; $i >= 0; $i--) {
      $if_stmt = $arms[$i];
      assert($if_stmt instanceof nodes\IfStmt);
      if ($php_stmt === null) {
        $php_stmt = $if_stmt;
      } else {
        assert($php_stmt instanceof nodes\IfStmt);
        $php_stmt = new nodes\IfStmt($if_stmt->test, $if_stmt->consequent, $php_stmt);
      }
    }

    $ctx->push_stmt($php_stmt);

    $ctx->pop_expr();
    $ctx->push_expr(new nodes\VariableExpr(array_pop($ctx->match_out_vars)));
  }

  private static function exit_match_discriminant(self $ctx): void {
    $expr = $ctx->pop_expr();

    if ($expr instanceof nodes\VariableExpr) {
      $php_var = $expr->variable;
    } else {
      $php_var = $ctx->php_tmp_var();
      $ctx->push_stmt(new nodes\AssignStmt($php_var, $expr));
    }
    array_push($ctx->match_in_vars, new nodes\VariableExpr($php_var));
  }

  private static function enter_match_arm(self $ctx, ir\nodes\MatchArm $node): void {
    $ctx->push_block();
    $accessors  = [ end($ctx->match_in_vars) ];
    $conditions = [];
    ir\Visitor::walk($node->pattern, [
      'VariantPattern' => function (ir\nodes\VariantPattern $node) use ($ctx, &$accessors, &$conditions) {
        $next_condition = new nodes\BinaryExpr(
          'instanceof',
          end($accessors),
          new nodes\ReferenceExpr($ctx->php_ref_from_ir_name($node->ref->tail_segment), false)
        );
        array_push($conditions, $next_condition);
      },
      'enter(NamedPatternField)' => function (ir\nodes\NamedPatternField $node) use ($ctx, &$accessors) {
        $next_accessor = new nodes\PropertyAccessExpr(
          end($accessors),
          $ctx->php_var_from_ir_name($node->name)
        );
        array_push($accessors, $next_accessor);
      },
      'exit(NamedPatternField)' => function () use (&$accessors) {
        array_pop($accessors);
      },
      'enter(OrderedVariantPatternField)' => function (ir\nodes\OrderedVariantPatternField $node) use ($ctx, &$accessors) {
        $next_accessor = new nodes\DynamicPropertyAccessExpr(
          end($accessors),
          new nodes\IntLiteral(IntegerValue::from_scalar($node->position))
        );
        array_push($accessors, $next_accessor);
      },
      'exit(OrderedVariantPatternField)' => function () use (&$accessors) {
        array_pop($accessors);
      },
      'StrConstPattern' => function (ir\nodes\StrConstPattern $node) use (&$accessors, &$conditions) {
        $next_condition = new nodes\BinaryExpr(
          '==',
          end($accessors),
          new nodes\StrLiteral($node->value)
        );
        array_push($conditions, $next_condition);
      },
      'FloatConstPattern' => function (ir\nodes\FloatConstPattern $node) use (&$accessors, &$conditions) {
        $next_condition = new nodes\BinaryExpr(
          '==',
          end($accessors),
          new nodes\FloatLiteral($node->value)
        );
        array_push($conditions, $next_condition);
      },
      'IntConstPattern' => function (ir\nodes\IntConstPattern $node) use (&$accessors, &$conditions) {
        $next_condition = new nodes\BinaryExpr(
          '==',
          end($accessors),
          new nodes\IntLiteral($node->value)
        );
        array_push($conditions, $next_condition);
      },
      'BoolConstPattern' => function (ir\nodes\BoolConstPattern $node) use (&$accessors, &$conditions) {
        $next_condition = new nodes\BinaryExpr(
          '==',
          end($accessors),
          new nodes\BoolLiteral($node->value)
        );
        array_push($conditions, $next_condition);
      },
      'VariablePattern' => function (ir\nodes\VariablePattern $node) use ($ctx, &$accessors) {
        $next_stmt = new nodes\AssignStmt(
          $ctx->php_var_from_ir_name($node->name),
          end($accessors)
        );
        $ctx->push_stmt($next_stmt);
      },
    ]);

    switch (count($conditions)) {
      case 0:
        $test = new nodes\BoolLiteral(BooleanValue::from_scalar(true));
        break;
      case 1:
        $test = $conditions[0];
        break;
      default:
        $test = $conditions[0];
        foreach (array_slice($conditions, 1) as $next) {
          $test = new nodes\BinaryExpr(
            '&&',
            $test,
            $next
          );
        }
    }

    array_push($ctx->match_tests, $test);
  }

  private static function exit_match_arm(self $ctx): void {
    $test    = array_pop($ctx->match_tests);
    $if_stmt = new nodes\IfStmt($test, $ctx->pop_block(), null);
    array_push($ctx->match_arms[count($ctx->match_arms) - 1], $if_stmt);
  }

  private static function enter_match_handler(self $ctx, ir\nodes\MatchHandler $node): void {
//    $ctx->push_block_exit_handler(function () {
//
//    });
  }

  private static function exit_match_handler(self $ctx, ir\nodes\MatchHandler $node): void {
//    $ctx->push_stmt(new nodes\SemiStmt($ctx->pop_expr()));
  }

  private static function enter_if_expr(self $ctx, ir\nodes\IfExpr $expr, ir\Path $path): void {
    $parent_ir_node = $path->parent->node;
    $return_type    = $expr->get('type');
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

  private static function exit_call_expr(self $ctx, ir\nodes\CallExpr $expr, ir\Path $path): void {
    if ($path->parent && $path->parent->node instanceof ir\nodes\PipeExpr && $expr === $path->parent->node->right) {
      return;
    }

    /**
     * A function call can be compiled in a few different ways:
     *
     * 2. A native function call. This solution is clean and efficient but can
     *    only be used when the compiler is certain that such a call will
     *    produce correct PHP. This solution can only be used when the callee
     *    has a known arity.
     *
     * 2. An inline closure that wraps available parameters and exposes a
     *    function interface for providing the rest of the parameters. This
     *    solution can only be used when the callee has a known arity.
     *
     * 3. An inline call to the `curry` function. This solution incurs a runtime
     *    performance penalty (an extra function call wrapping the *real* call)
     *    but is necessary at any call-sites where the arity of the callee is
     *    not known at compile time.
     */

    $callee_arity = $expr->callee->get('arity');
    assert($callee_arity instanceof ir\arity\Arity);

    $total_args = count($expr->args);
    $args       = $ctx->pop_exprs($total_args);
    $callee     = $ctx->pop_expr();
    if ($callee_arity instanceof ir\arity\KnownArity) {
      $ctx->push_expr(self::over_applied_call_site($ctx, $callee, $args, $callee_arity));
    } else {
      $ctx->push_expr(self::curry_call_site($ctx, $callee, $args));
    }
  }

  /**
   * @param self                $ctx
   * @param nodes\Expr          $callee
   * @param nodes\Expr[]        $args
   * @param ir\arity\KnownArity $arity
   * @return nodes\Expr
   */
  private static function under_applied_call_site(self $ctx, nodes\Expr $callee, array $args, ir\arity\KnownArity $arity): nodes\Expr {
    $func_scope = new names\ClosureScope($ctx->current_function_scope());
    array_push($ctx->function_scopes, $func_scope);

    /* @var nodes\FuncParam[] $closure_params */
    $closure_params = [];
    $leftover_args  = $arity->params - count($args);
    for ($i = 0; $i < $leftover_args; $i++) {
      $var              = $ctx->php_tmp_var();
      $closure_params[] = nodes\FuncParam::from_var($var);
      $args[]           = new nodes\VariableExpr($var);
    }

    array_pop($ctx->function_scopes);
    $closure_body = new nodes\CallExpr($callee, $args);
    return new nodes\ArrowExpr($closure_params, $closure_body);
  }

  private static function fully_applied_call_site(nodes\Expr $callee, array $args): nodes\Expr {
    return new nodes\CallExpr($callee, $args);
  }

  /**
   * @param self           $ctx
   * @param nodes\Expr     $callee
   * @param nodes\Expr[]   $args
   * @param ir\arity\Arity $arity
   * @return nodes\Expr
   */
  private static function over_applied_call_site(self $ctx, nodes\Expr $callee, array $args, ir\arity\Arity $arity): nodes\Expr {
    if (($arity instanceof ir\arity\KnownArity) === false) {
      return self::curry_call_site($ctx, $callee, $args);
    }

    while (count($args) > 0 && count($args) >= $arity->params) {
      if (($arity instanceof ir\arity\KnownArity) === false) {
        return self::curry_call_site($ctx, $callee, $args);
      } else if ($arity->params === 0) {
        return $callee;
      }

      $taken_args = array_splice($args, 0, $arity->params);
      $arity      = $arity->apply_arguments(count($taken_args));
      $callee     = self::fully_applied_call_site($callee, $taken_args);
    }

    if (!empty($args) && $arity instanceof ir\arity\KnownArity) {
      if (($callee instanceof nodes\ReferenceExpr) === false) {
        /**
         * If the callee is more complex than a reference expression then it
         * could have side-effects. If the callee has side effects and it's
         * wrapped in an under-application closure, when those side effects
         * occur will change which could change the behavior of the program.
         *
         * The solution is to recognize this case and bind the result of the
         * callee to a temporary variable and use the temporary variable as the
         * callee inside of the under-application closure.
         */
        $tmp_var  = $ctx->php_tmp_var();
        $tmp_stmt = new nodes\AssignStmt($tmp_var, $callee);
        $ctx->push_stmt($tmp_stmt);
        $callee = new nodes\VariableExpr($tmp_var);
      }

      $callee = self::under_applied_call_site($ctx, $callee, $args, $arity);
    }

    return $callee;
  }

  /**
   * @param Lower        $ctx
   * @param nodes\Expr   $callee
   * @param nodes\Expr[] $args
   * @return nodes\Expr
   */
  private static function curry_call_site(self $ctx, nodes\Expr $callee, array $args): nodes\Expr {
    $curry_ref = $ctx->reference_helper('curry');
    return new nodes\CallExpr(
      new nodes\ReferenceExpr($curry_ref, false),
      [
        $callee,
        new nodes\OrderedArrayExpr($args),
      ]);
  }

  private static function exit_pipe_expr(self $ctx, ir\nodes\PipeExpr $expr): void {
    if ($expr->right instanceof ir\nodes\CallExpr) {
      $rhs_args   = $ctx->pop_exprs(count($expr->right->args));
      $rhs_callee = $ctx->pop_expr();
      $lhs        = $ctx->pop_expr();
      $rhs_arity  = $expr->right->callee->get('arity');
      $expr       = self::over_applied_call_site($ctx, $rhs_callee, array_merge($rhs_args, [ $lhs ]), $rhs_arity);
    } else {
      $rhs       = $ctx->pop_expr();
      $lhs       = $ctx->pop_expr();
      $rhs_arity = $expr->right->get('arity');
      $expr      = self::over_applied_call_site($ctx, $rhs, [ $lhs ], $rhs_arity);
    }
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
      case '^':
        return '**';
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

  private static function exit_variant_constructor_expr(self $ctx, ir\nodes\VariantConstructorExpr $expr): void {
    $ref = new nodes\ReferenceExpr($ctx->php_ref_from_ir_name($expr->ref->tail_segment), false);
    if ($expr->fields instanceof ir\nodes\NamedVariantConstructorFields) {
      $fields = [];
      $exprs  = $ctx->pop_exprs(count($expr->fields->pairs));
      foreach ($expr->fields->pairs as $index => $field) {
        $field_name = $ctx->php_name_from_ir_name($field->name);
        $field_expr = $exprs[$index];
        $fields[]   = new nodes\FieldNode($field_name, $field_expr);
      }
      $args = [ new nodes\AssociativeArrayExpr($fields) ];
    } else if ($expr->fields instanceof ir\nodes\OrderedVariantConstructorFields) {
      $args = $ctx->pop_exprs(count($expr->fields->order));
    } else {
      $args = [];
    }
    $ctx->push_expr(new nodes\NewExpr($ref, $args));
  }

  private static function ref_expr(self $ctx, ir\nodes\RefExpr $expr, ir\Path $path): void {
    $ir_name   = $expr->ref->tail_segment;
    $ir_symbol = $ir_name->get('symbol');
    if ($ir_symbol instanceof ir\names\VarSymbol) {
      $php_var  = $ir_symbol->get('php/var');
      $php_expr = new nodes\VariableExpr($php_var);
    } else {
      $php_ref   = $ir_symbol->get('php/ref');
      $is_quoted = !(
        $path->parent &&
        $path->parent->node instanceof ir\nodes\CallExpr &&
        $path->parent->node->callee === $expr
      );
      $php_expr  = new nodes\ReferenceExpr($php_ref, $is_quoted);
    }
    $ctx->push_expr($php_expr);
  }

  private static function str_literal(self $ctx, ir\nodes\StrLiteral $expr): void {
    $php_expr = new nodes\StrLiteral($expr->value);
    $ctx->push_expr($php_expr);
  }

  private static function float_literal(self $ctx, ir\nodes\FloatLiteral $expr): void {
    $php_expr = new nodes\FloatLiteral($expr->value);
    $ctx->push_expr($php_expr);
  }

  private static function int_literal(self $ctx, ir\nodes\IntLiteral $expr): void {
    $php_expr = new nodes\IntLiteral($expr->value);
    $ctx->push_expr($php_expr);
  }

  private static function bool_literal(self $ctx, ir\nodes\BoolLiteral $expr): void {
    $php_expr = new nodes\BoolLiteral($expr->value);
    $ctx->push_expr($php_expr);
  }

  private static function unit_literal(self $ctx): void {
    $php_expr = new nodes\NullLiteral();
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
      case 'cast_int_to_string':
      case 'cast_float_to_string':
        return new nodes\CastExpr('string', $args[0]);
      default:
        die("unknown PHP construct: $name");
    }
  }
}
