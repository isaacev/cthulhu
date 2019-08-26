<?php

namespace Cthulhu\Analysis;

use Cthulhu\AST;
use Cthulhu\IR;
use Cthulhu\Types;

/**
 * Analyzer takes an abstract syntax tree (AST) and returns a type-annotated
 * intermediate representation (IR). In addition to type annotations, the IR
 * contains information about which variables are declared in each scope and
 * allows for easier generation of PHP code.
 */
class Analyzer {
  private $scopes;
  private $module_scopes;

  function __construct() {
    $this->scopes = [];
    $this->module_scopes = [];
  }

  private function push_scope(IR\Scope $scope): IR\Scope {
    array_push($this->scopes, $scope);
    if ($scope instanceof IR\ModuleScope) {
      array_push($this->module_scopes, $scope);
    }
    return $scope;
  }

  private function peek_scope(): IR\Scope {
    return $this->scopes[count($this->scopes) - 1];
  }

  private function peek_module_scope(): IR\ModuleScope {
    return $this->module_scopes[count($this->module_scopes) - 1];
  }

  private function pop_scope(): IR\Scope {
    $scope = array_pop($this->scopes);
    if ($scope instanceof IR\ModuleScope) {
      array_pop($this->module_scopes);
    }
    return $scope;
  }

  public function analyze(AST\RootNode $root_node): IR\SourceModule {
    // Build module scope with appropriate pointers to other scopes
    $parent_scope = null;
    $block_scope = new IR\BlockScope();
    $module_scope = new IR\ModuleScope($parent_scope, null, $block_scope);

    // Add module scope to pending scope stack
    $this->push_scope($module_scope);

    // Analyze statements inside the module
    $block = $this->block_node($root_node->block, $block_scope);

    // Remove module scope from pending scope stack
    $this->pop_scope();

    // Build IR node representing entire module
    return new IR\SourceModule($module_scope, $block);
  }

  private function block_node(AST\BlockNode $block, ?IR\BlockScope $block_scope = null): IR\BlockNode {
    if ($block_scope === null) {
      $block_scope = $this->push_scope(new IR\BlockScope($this->peek_scope()));
    } else {
      $this->push_scope($block_scope);
    }

    $stmts = [];
    foreach ($block->stmts as $stmt) {
      $stmts[] = $this->stmt($stmt);
    }
    $this->pop_scope(); // popping the block scope
    return new IR\BlockNode($block_scope, $stmts);
  }

  private function stmt(AST\Stmt $stmt): IR\Stmt {
    switch (true) {
      case $stmt instanceof AST\ModuleStmt:
        return $this->module_stmt($stmt);
      case $stmt instanceof AST\LetStmt:
        return $this->let_stmt($stmt);
      case $stmt instanceof AST\ExprStmt:
        return $this->expr_stmt($stmt);
      default:
        throw new \Exception('unknown statement: ' . get_class($stmt));
    }
  }

  private function module_stmt(AST\ModuleStmt $stmt): IR\ModuleStmt {
    // Build submodule scope with appropriate pointers to other scopes
    $parent_scope = $this->peek_module_scope();
    $block_scope = new IR\BlockScope();
    $submodule_scope = new IR\ModuleScope($parent_scope, $stmt->name->ident, $block_scope);
    $parent_scope->add_submodule($submodule_scope);

    // Add module scope to pending scope stack for lookup while analyzing inner statements
    $this->push_scope($submodule_scope);

    // Analyze statements inside the module
    $block = $this->block_node($stmt->block, $block_scope);

    // Remove module scope from pending scope stack
    $this->pop_scope();

    // Build IR node representing the module definition
    return new IR\ModuleStmt($submodule_scope->identifier, $submodule_scope, $block);
  }

  private function let_stmt(AST\LetStmt $stmt): IR\AssignStmt {
    $expr = $this->expr($stmt->expr);
    $ident = $this->peek_scope()->new_binding($stmt->name, $expr->type());
    return new IR\AssignStmt($ident, $expr);
  }

  private function expr_stmt(AST\ExprStmt $stmt): IR\ExprStmt {
    $expr = $this->expr($stmt->expr);
    return new IR\ExprStmt($expr);
  }

  private function expr(AST\Expr $expr): IR\Expr {
    switch (true) {
      case $expr instanceof AST\FuncExpr:
        return $this->func_expr($expr);
      case $expr instanceof AST\IfExpr:
        return $this->if_expr($expr);
      case $expr instanceof AST\CallExpr:
        return $this->call_expr($expr);
      case $expr instanceof AST\MemberExpr:
        return $this->member_expr($expr);
      case $expr instanceof AST\BinaryExpr:
        return $this->binary_expr($expr);
      case $expr instanceof AST\PathExpr:
        return $this->path_expr($expr);
      case $expr instanceof AST\StrExpr:
        return $this->str_expr($expr);
      case $expr instanceof AST\NumExpr:
        return $this->num_expr($expr);
      default:
        throw new \Exception('unknown expression: ' . get_class($expr));
    }
  }

  private function func_expr(AST\FuncExpr $expr): IR\FuncExpr {
    $func_scope = $this->push_scope(new IR\FuncScope($this->peek_scope()));
    $params = array_map(function ($pair) use ($func_scope) {
      $ident = new IR\IdentifierNode($pair['name']);
      $type = $this->annotation_to_type($pair['annotation']);
      $symbol = $func_scope->new_binding($ident, $type);
      return new IR\ParamNode($ident, $symbol);
    }, $expr->params);
    $return_type = $this->annotation_to_type($expr->return_annotation);
    $block = $this->block_node($expr->block);
    $this->pop_scope(); // popping the function scope
    if ($return_type->accepts($block->type()) === false) {
      throw new Types\Errors\TypeMismatch($return_type, $block->type());
    }
    return new IR\FuncExpr($params, $return_type, $block);
  }

  private function if_expr(AST\IfExpr $expr): IR\IfExpr {
    $condition = $this->expr($expr->condition);
    if (($condition->type() instanceof Types\BoolType) === false) {
      throw new Types\Errors\TypeMismatch(new Types\BoolType(), $condition->type());
    }

    $if_block = $this->block_node($expr->if_clause);
    $else_block = $expr->else_clause ? $this->block_node($expr->else_clause) : null;

    if ($expr->else_clause === null) {
      // If there isn't an else-clause, require that
      // the return type of the if-clause be Void.
      if (($if_block->type() instanceof Types\VoidType) === false) {
        throw new Types\Errors\TypeMismatch(new Types\VoidType(), $if_block->type());
      }

      return new IR\IfExpr(new Types\VoidType(), $condition, $if_block, null);
    }

    // If there is an else-clause, require that the if-clause and the
    // else-clause have an equivalent return type.
    if ($if_block->type()->accepts($else_block->type())) {
      return new IR\IfExpr($if_block->type(), $condition, $if_block, $else_block);
    }

    throw new Types\Errors\TypeMismatch($if_block->type(), $else_block->type());
  }

  private function call_expr(AST\CallExpr $expr): IR\CallExpr {
    $callee = $this->expr($expr->callee);
    if (($callee->type() instanceof Types\FuncType) === false) {
      throw new Types\Errors\TypeMismatch('function', $callee->type());
    }
    $args = [];
    foreach ($expr->args as $arg) {
      $args[] = $this->expr($arg);
    }
    return new IR\CallExpr($callee, $args);
  }

  private function member_expr(AST\MemberExpr $expr): IR\MemberExpr {
    // TODO
    // $object = $this->expr($expr->object);
    // $property = $expr->property->name;
    // $type = $object->type()->member($property);
    // return new IR\MemberExpr($type, $object, $property);
  }

  private function binary_expr(AST\BinaryExpr $expr): IR\BinaryExpr {
    $left = $this->expr($expr->left);
    $right = $this->expr($expr->right);
    $type = $left->type()->binary_operator($expr->operator, $right->type());
    return new IR\BinaryExpr($type, $expr->operator, $left, $right);
  }

  private function path_expr(AST\PathExpr $expr): IR\PathExpr {
    if ($expr->length() === 1) {
      $binding = $this->peek_scope()->get_binding($expr->nth(0)->ident);
      return new IR\PathExpr([ $binding->identifier ], $binding->type);
    }

    $segments = [];
    $module_scope = $this->peek_module_scope();
    for ($n = 0, $len = $expr->length(); $n < $len; $n++) {
      if ($n < $len - 1) {
        // Segment isn't the last segment so treat as a submodule reference
        $module_scope = $module_scope->get_submodule($expr->nth($n)->ident);
        $segments[] = $module_scope->identifier;
      } else {
        // Segment is last segment so treat as a variable reference
        $binding = $module_scope->get_binding($expr->nth($n)->ident);
        $segments[] = $binding->identifier;
        return new IR\PathExpr($segments, $binding->type);
      }
    }
  }

  private function str_expr(AST\StrExpr $expr): IR\StrExpr {
    return new IR\StrExpr($expr->value);
  }

  private function num_expr(AST\NumExpr $expr): IR\NumExpr {
    return new IR\NumExpr($expr->value);
  }

  private function annotation_to_type(AST\Annotation $annotation): Types\Type {
    if ($annotation instanceof AST\NamedAnnotation) {
      switch ($annotation->name) {
        case 'Void':
          return new Types\VoidType();
        case 'Num':
          return new Types\NumType();
        case 'Str':
          return new Types\StrType();
        case 'Bool':
          return new Types\BoolType();
        default:
          throw new Types\Errors\UnknownType($annotation->name);
      }
    }

    throw new Types\Errors\UnknownType("$annotation");
  }
}
