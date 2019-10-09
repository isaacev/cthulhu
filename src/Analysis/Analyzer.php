<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\IR;

class Analyzer {
  public static function ast_to_program(AST\File $file): IR\Program {
    $linker = new Linker();
    $root_module = self::ast_to_module($linker, $file);

    // Check if the root module has a `main` function
    foreach ($root_module->items as $item) {
      if ($item instanceof IR\FnItem && $item->symbol->name === 'main') {
        return new IR\Program($root_module, $linker->includes, $item->symbol);
      }
    }

    throw Errors::no_entry_point($file->file);
  }

  public static function ast_to_module(Linker $linker, AST\File $file): IR\SourceModule {
    $ctx = new Context($linker, $file->file);
    $items = [];
    foreach ($file->items as $item) {
      $items[] = self::item($ctx, $item);
    }
    return new IR\SourceModule($file->file, $ctx->pop_module_scope(), $items);
  }

  private static function item(Context $ctx, AST\Item $item): IR\Item {
    switch (true) {
      case $item instanceof AST\UseItem:
        return self::use_item($ctx, $item);
      case $item instanceof AST\ModItem:
        return self::mod_item($ctx, $item);
      case $item instanceof AST\FnItem:
        return self::fn_item($ctx, $item);
      case $item instanceof AST\NativeItem:
        return self::native_item($ctx, $item);
      default:
        throw new \Exception('illegal item in module');
    }
  }

  private static function use_item(Context $ctx, AST\UseItem $item): IR\UseItem {
    $binding = self::path_to_binding($ctx, $item->path);
    $ctx->current_module_scope()->add_binding($binding);
    return new IR\UseItem($binding->symbol, $item->attrs);
  }

  private static function mod_item(Context $ctx, AST\ModItem $item): IR\ModItem {
    $name = $item->name;
    $ctx->push_module_scope($name);
    $items = [];
    foreach ($item->items as $item) {
      $items[] = self::item($ctx, $item);
    }
    $attrs = self::attrs($item->attrs);
    $scope = $ctx->pop_module_scope();
    $ctx->current_module_scope()->add_binding(IR\Binding::for_module($scope));
    return new IR\ModItem($scope, $items, $attrs);
  }

  private static function fn_item(Context $ctx, AST\FnItem $item): IR\FnItem {
    $fn_name = $item->name->ident;
    $origin = $item->span->extended_to($item->returns->span);
    $symbol = new IR\Symbol($fn_name, $origin, $ctx->current_module_scope()->symbol);

    $param_notes = array_map(function ($param) { return $param->note; }, $item->params);
    $generic_names = self::find_all_generic_names($item->returns, ...$param_notes);
    $generic_table = [];
    foreach ($generic_names as $name) {
      $generic_table[$name] = new IR\Types\GenericType($name);
    }
    $ctx->push_generic_table($generic_table);

    // Determine function type signature
    $param_types = [];
    foreach ($item->params as $param) {
      $param_types[] = self::annotation_to_type($ctx, $param->note);
    }
    $return_origin = $item->returns->span;
    $return_type = self::annotation_to_type($ctx, $item->returns);
    $type = new IR\Types\FunctionType($param_types, $return_type);
    $ctx->current_module_scope()->add_binding(IR\Binding::for_value($symbol, $type));

    // Build new block scope and add parameters to the scope
    $ctx->push_block_scope();
    $param_symbols = [];
    foreach ($item->params as $index => $param) {
      $param_name = $param->name->ident;
      $param_origin = $param->span;
      $param_symbol = new IR\Symbol($param_name, $param_origin, null);
      $param_symbols[] = $param_symbol;
      $param_type = $param_types[$index];
      $ctx->current_block_scope()->add_binding(IR\Binding::for_value($param_symbol, $param_type));
    }

    // Verify that the function body returns the correct type
    $ctx->push_expected_return($item, $return_type);
    $body = self::block($ctx, $item->body);
    $found_type = $body->return_type();
    $ctx->pop_expected_return();

    if ($return_type->equals($found_type) === false) {
      // This condition is necessary because even though the `$return_type` was
      // pushed onto the expected return stack, that stack is only read when an
      // `AST\SemiStmt` (implicit return) is encountered. If the block is has
      // a branch that returns *nothing* and the return type is not `()`, this
      // check will catch and report those errors.
      $block_span = $item->body->span;
      $wanted_span = $item->returns->span;
      $wanted_type = $return_type;
      $last_stmt = $body->last_stmt();
      $last_ast_stmt = end($item->body->stmts);
      $last_semi = $last_ast_stmt instanceof AST\SemiStmt ? $last_ast_stmt->semi->span : null;
      throw Errors::function_returns_nothing($ctx->file, $block_span, $wanted_span, $wanted_type, $last_stmt, $last_semi);
    }

    $ctx->pop_generic_table();
    $attrs = self::attrs($item->attrs);
    return new IR\FnItem($symbol, $param_symbols, $type, $ctx->pop_block_scope(), $body, $attrs);
  }

  private static function native_item(Context $ctx, AST\NativeItem $item): IR\NativeItem {
    $name = $item->name->ident;
    $internal = new IR\Symbol($name, $item->name->span, $ctx->current_module_scope()->symbol);
    $external = new IR\Symbol($name, $item->name->span, null);
    $type = self::annotation_to_type($ctx, $item->note);
    $ctx->current_module_scope()->add_binding(IR\Binding::for_value($internal, $type));
    $attrs = self::attrs($item->attrs);
    return new IR\NativeItem($internal, $external, $type, $attrs);
  }

  private static function attrs(array $attr_nodes): array {
    $attrs = [];
    foreach ($attr_nodes as $attr_node) {
      $attrs[$attr_node->name] = true;
    }
    return $attrs;
  }

  private static function block(Context $ctx, AST\BlockNode $block): IR\BlockNode {
    $ctx->push_block_scope();
    $stmts = [];
    $total_stmts = count($block->stmts);
    foreach ($block->stmts as $stmt) {
      $stmts[] = self::stmt($ctx, $stmt);
    }

    if (empty($stmts)) {
      $type = new IR\Types\UnitType();
    } else {
      $last_stmt = end($stmts);
      if ($last_stmt instanceof IR\ReturnStmt) {
        $type = $last_stmt->expr->return_type();
      } else {
        $type = new IR\Types\UnitType();
      }
    }

    return new IR\BlockNode($type, $ctx->pop_block_scope(), $stmts);
  }

  private static function stmt(Context $ctx, AST\Stmt $stmt): IR\Stmt {
    switch (true) {
      case $stmt instanceof AST\LetStmt:
        return self::let_stmt($ctx, $stmt);
      case $stmt instanceof AST\SemiStmt:
        return self::semi_stmt($ctx, $stmt);
      case $stmt instanceof AST\ExprStmt:
        return self::expr_stmt($ctx, $stmt);
      default:
        throw new \Exception('illegal statement in block');
    }
  }

  private static function let_stmt(Context $ctx, AST\LetStmt $stmt): IR\Stmt {
    $name = $stmt->name->ident;
    $origin = $stmt->name->span;
    $symbol = new IR\Symbol($name, $origin, null);
    $note_type = $stmt->note ? self::annotation_to_type($ctx, $stmt->note) : null;
    $expr = self::expr($ctx, $stmt->expr);
    $expr_type = $expr->return_type();

    if ($note_type !== null && $note_type->equals($expr_type) === false) {
      throw Errors::binding_disagrees_with_expr(
        $ctx->file,
        $note_type,
        $stmt->note->span,
        $expr_type,
        $stmt->expr->span
      );
    }

    $ctx->current_block_scope()->add_binding(IR\Binding::for_value($symbol, $expr->return_type()));
    return new IR\AssignStmt($symbol, $expr);
  }

  private static function semi_stmt(Context $ctx, AST\SemiStmt $stmt): IR\Stmt {
    $expr = self::expr($ctx, $stmt->expr);
    return new IR\SemiStmt($expr);
  }

  private static function expr_stmt(Context $ctx, AST\ExprStmt $stmt): IR\Stmt {
    $expr = self::expr($ctx, $stmt->expr);
    return new IR\ReturnStmt($expr);
  }

  private static function expr(Context $ctx, AST\Expr $expr): IR\Expr {
    switch (true) {
      case $expr instanceof AST\IfExpr:
        return self::if_expr($ctx, $expr);
      case $expr instanceof AST\CallExpr:
        return self::call_expr($ctx, $expr);
      case $expr instanceof AST\BinaryExpr:
        return self::binary_expr($ctx, $expr);
      case $expr instanceof AST\UnaryExpr:
        return self::unary_expr($ctx, $expr);
      case $expr instanceof AST\PathExpr:
        return self::path_expr($ctx, $expr);
      case $expr instanceof AST\StrExpr:
        return self::str_expr($ctx, $expr);
      case $expr instanceof AST\NumExpr:
        return self::num_expr($ctx, $expr);
      case $expr instanceof AST\BoolExpr:
        return self::bool_expr($ctx, $expr);
    }
  }

  // fn_expr

  /**
   * If-expression analysis
   *
   * - If the true-branch of an if-expression returns a value, all other
   *   branches must also return a value with a compatible type.
   * - If the false-branch is not given, the else-branch is treated as if it
   *   does not return a type.
   */
  private static function if_expr(Context $ctx, AST\IfExpr $expr): IR\Expr {
    $cond = self::expr($ctx, $expr->condition);
    if (IR\Types\BoolType::not_equal_to($cond->return_type())) {
      $found_span = $expr->condition->span;
      $found_type = $cond->return_type();
      throw Errors::condition_not_bool($ctx->file, $found_span, $found_type);
    }

    $if_true = self::block($ctx, $expr->if_clause);
    $if_true_type = $if_true->return_type();

    if ($expr->else_clause !== null) {
      $if_false = self::block($ctx, $expr->else_clause);
      $if_false_type = $if_false->return_type();
      if ($if_true_type->equals($if_false_type) === false) {
        $if_true_span = $expr->if_clause->span;

        // When determining which span to use for both blocks, if the block
        // implicitly returns its last statement, use the span from that
        // statement. If the block is empty or doesn't have an implicit return,
        // use the span of the entire block.
        $if_true_span = $expr->if_clause->returns()
          ? $expr->if_clause->last_stmt()->span
          : $expr->if_clause->span;

        $if_false_span = $expr->else_clause->returns()
          ? $expr->else_clause->last_stmt()->span
          : $expr->else_clause->span;

        throw Errors::incompatible_if_and_else_types(
          $ctx->file,
          $if_true_span,
          $if_true_type,
          $if_false_span,
          $if_false_type);
      }
    } else {
      // The if-expression doesn't have a false-block, this means the block
      // implicitly returns the unit type. If the true-block returns a non-unit
      // type, throw an error because of type incompatibility.
      if (($if_true_type instanceof IR\Types\UnitType) === false) {
        // When determining which span to use for both blocks, if the block
        // implicitly returns its last statement, use the span from that
        // statement. If the block is empty or doesn't have an implicit return,
        // use the span of the entire block.
        $if_true_span = $expr->if_clause->returns()
          ? $expr->if_clause->last_stmt()->span
          : $expr->if_clause->span;

        $if_true_block_span = $expr->if_clause->span;

        throw Errors::if_block_incompatible_with_void(
          $ctx->file,
          $if_true_span,
          $if_true_type,
          $if_true_block_span);
      }
    }

    return new IR\IfExpr($if_true_type, $cond, $if_true, $if_false);
  }

  private static function call_expr(Context $ctx, AST\CallExpr $expr): IR\CallExpr {
    $callee = self::expr($ctx, $expr->callee);
    $unbound_callee_type = $callee->return_type();
    if (IR\Types\FunctionType::not_equal_to($unbound_callee_type)) {
      throw new Types\Errors\TypeMismatch('function', $callee->return_type());
      throw Errors::called_a_non_function(
        $ctx->file,
        $expr->callee->span,
        $callee->return_type()
      );
    }

    $num_args_wanted = count($unbound_callee_type->inputs);
    $num_args_given = count($expr->args);
    if ($num_args_wanted !== $num_args_given) {
      throw Errors::func_called_with_wrong_num_or_args(
        $ctx->file,
        $expr->span,
        $num_args_given,
        $callee->return_type()
      );
    }

    $args = [];
    for ($i = 0; $i < $num_args_wanted; $i++) {
      $wanted_type = $callee->return_type()->inputs[$i];
      $args[] = $given_expr = self::expr($ctx, $expr->args[$i]);
      if (($wanted_type->equals($given_expr->return_type())) === false) {
        throw Errors::type_mismatch(
          $ctx->file,
          $given_expr->return_type(),
          $expr->args[$i]->span,
          $wanted_type);
      }
    }

    return new IR\CallExpr($callee, $args);
  }

  private static function binary_expr(Context $ctx, AST\BinaryExpr $expr): IR\Expr {
    $left = self::expr($ctx, $expr->left);
    $right = self::expr($ctx, $expr->right);
    $lhs = $left->return_type();
    $rhs = $right->return_type();
    $ret = $lhs->get_binop($expr->operator, $rhs);
    if ($ret === null) {
      throw Errors::unsupported_binary_operator(
        $ctx->file,
        $expr->span,
        $expr->operator,
        $left->return_type()
      );
    }
    return new IR\BinaryExpr($ret, $expr->operator, $left, $right);
  }

  private static function unary_expr(Context $ctx, AST\UnaryExpr $expr): IR\UnaryExpr {
    $operand = self::expr($ctx, $expr->operand);
    $type = $operand->return_type()->unary_operator($expr->operator); // FIXME
    return new IR\UnaryExpr($type, $expr->operator, $operand);
  }

  private static function path_expr(Context $ctx, AST\PathExpr $expr): IR\ReferenceExpr {
    $binding = self::path_to_binding($ctx, $expr->path);
    return new IR\ReferenceExpr($binding->as_value(), $binding->symbol);
  }

  private static function str_expr(Context $ctx, AST\StrExpr $expr): IR\StrExpr {
    $type = $ctx->raw_path_to_type('kernel', 'Str');
    return new IR\StrExpr($type, $expr->value);
  }

  private static function num_expr(Context $ctx, AST\NumExpr $expr): IR\NumExpr {
    $type = $ctx->raw_path_to_type('kernel', 'Num');
    return new IR\NumExpr($type, $expr->value);
  }

  private static function bool_expr(Context $ctx, AST\BoolExpr $expr): IR\BoolExpr {
    $type = $ctx->raw_path_to_type('kernel', 'Bool');
    return new IR\BoolExpr($type, $expr->value);
  }

  // int_expr

  /**
   * Utility methods
   */
  private static function annotation_to_type(Context $ctx, AST\Annotation $note): IR\Types\Type {
    switch (true) {
      case $note instanceof AST\GroupedAnnotation:
        return self::annotation_to_type($ctx, $note->inner);
      case $note instanceof AST\FunctionAnnotation:
        return self::function_annotation_to_type($ctx, $note);
      case $note instanceof AST\UnitAnnotation:
        return new IR\Types\UnitType();
      case $note instanceof AST\NamedAnnotation:
        return self::named_annotation_to_type($ctx, $note);
      case $note instanceof AST\GenericAnnotation:
        return self::generic_annotation_to_type($ctx, $note);
      default:
        throw new \Exception('unknown annotation type');
    }
  }

  private static function function_annotation_to_type(Context $ctx, AST\FunctionAnnotation $note): IR\Types\Type {
    $inputs = array_map(function ($note) use ($ctx) {
      return self::annotation_to_type($ctx, $note);
    }, $note->inputs);
    $output = self::annotation_to_type($ctx, $note->output);
    return new IR\Types\FunctionType($inputs, $output);
  }

  private static function named_annotation_to_type(Context $ctx, AST\NamedAnnotation $note): IR\Types\Type {
    $binding = self::path_to_binding($ctx, $note->path);
    return $binding->as_type();
  }

  private static function generic_annotation_to_type(Context $ctx, AST\GenericAnnotation $note): IR\Types\Type {
    if ($type = $ctx->lookup_generic($note->name)) {
      return $type;
    }
    throw Error::unknown_generic_type(
      $ctx->file,
      $note->span,
      $note->name
    );
  }

  private static function find_all_generic_names(AST\Annotation ...$types): array {
    $names = [];
    while ($next = array_pop($types)) {
      switch (true) {
        case $next instanceof AST\GenericAnnotation:
          $names[] = $next->name;
          break;
      }
    }
    return array_unique($names);
  }

  private static function path_to_binding(Context $ctx, AST\PathNode $path): IR\Binding {
    if ($path->extern) {
      $starting_scope = $ctx->extern_scope();
    } else if ($ctx->has_block_scopes()) {
      $starting_scope = $ctx->current_block_scope();
    } else {
      $starting_scope = $ctx->current_module_scope();
    }

    $total_segments = count($path->segments);
    $intermediate_scope = $starting_scope;
    for ($i = 0; $i < $total_segments; $i++) {
      $segment = $path->segments[$i];
      $is_last_segment = $i + 1 === $total_segments;

      if ($is_last_segment) {
        if ($binding = $intermediate_scope->resolve_name($segment->ident)) {
          return $binding;
        } else if ($intermediate_scope instanceof IR\BlockScope) {
          throw Errors::unknown_local_variable(
            $ctx->file,
            $segment->span,
            $segment->ident
          );
        } else if ($intermediate_scope instanceof Linker) {
          throw Errors::unknown_external_module(
            $ctx->file,
            $segment->span,
            $segment->ident
          );
        } else {
          throw Errors::unknown_module_field(
            $ctx->file,
            $intermediate_scope->symbol,
            $segment->span,
            $segment->ident
          );
        }
      }

      $binding = $intermediate_scope->resolve_name($segment->ident);
      if ($binding === null) {
        if ($intermediate_scope instanceof Linker) {
          throw Errors::unknown_external_module(
            $ctx->file,
            $segment->span,
            $segment->ident
          );
        } else if ($intermediate_scope instanceof IR\ModuleScope) {
          throw Errors::unknown_submodule(
            $ctx->file,
            $intermediate_scope->symbol,
            $segment->span,
            $segment->ident
          );
        } else {
          throw Errors::unknown_local_variable(
            $ctx->file,
            $segment->span,
            $segment->ident
          );
        }
      }

      if ($binding->kind === 'module') {
        $intermediate_scope = $binding->as_scope();
        continue;
      }

      throw Errors::value_referenced_as_module(
        $ctx->file,
        $path->span->extended_to($segment->span),
        $binding->symbol,
        $binding->as_value(),
        $path->span
      );
    }
  }
}
