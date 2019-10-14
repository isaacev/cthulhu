<?php

namespace Cthulhu\php\names;

use Cthulhu\ir;
use Cthulhu\php;

class Renamer {
  protected $name_to_symbol;
  protected $symbol_to_name;
  protected $renamed_symbols;
  protected $root_scope;
  protected $namespace_scopes = [];
  protected $namespace_refs = [];
  protected $function_signatures = [];
  protected $function_scopes = [];

  function __construct(ir\Table $name_to_symbol, ir\Table $symbol_to_name) {
    $this->name_to_symbol = $name_to_symbol;
    $this->symbol_to_name = $symbol_to_name;
    $this->renamed_symbols = new ir\Table();
    $this->root_scope = new Scope();
  }

  public function enter_namespace(ir\nodes\Name $name): void {
    array_push($this->namespace_refs, $this->ref_from_name($name));
    array_push($this->namespace_scopes, new Scope());
  }

  public function exit_namespace(): php\nodes\Reference {
    array_pop($this->namespace_scopes);
    return array_pop($this->namespace_refs);
  }

  public function current_namespace_scope(): ?Scope {
    return empty($this->namespace_scopes) ? null : end($this->namespace_scopes);
  }

  public function current_function_scope(): ?Scope {
    return empty($this->function_scopes) ? null : end($this->function_scopes);
  }

  public function enter_function(ir\nodes\Name $name, array $params): void {
    $name = $this->name_from_name($name);
    $scope = new Scope();
    array_push($this->function_scopes, $scope);
    $params = array_map(function ($param) use ($scope) {
      $var = $this->var_from_name($param->name);
      $scope->use_name($var->value);
      return $var;
    }, $params);
    array_push($this->function_signatures, [
      $name,
      $params,
    ]);
  }

  public function exit_function(): array {
    array_pop($this->function_scopes);
    return array_pop($this->function_signatures);
  }

  public function native_function(ir\nodes\Name $name, int $num_params): array {
    $name = $this->name_from_name($name);
    $scope = new Scope();
    array_push($this->function_scopes, $scope);
    $params = [];
    for ($i = 0; $i < $num_params; $i++) {
      $value = $this->allocate_tmp();
      $params[] = new php\nodes\Variable($value);
    }
    array_pop($this->function_scopes);
    return [
      $name,
      $params,
    ];
  }

  public function name_to_ref_expr(ir\nodes\Name $name): php\nodes\Expr {
    $symbol = $this->name_to_symbol->get($name);
    if (($symbol instanceof ir\names\RefSymbol) === false) {
      throw new \Exception('cannot convert local variable to reference expression');
    }

    $segments = [ $this->renamed_symbols->get($symbol) ];
    while ($symbol = $symbol->parent) {
      array_unshift($segments, $this->renamed_symbols->get($symbol));
    }
    return new php\nodes\ReferenceExpr(
      new php\nodes\Reference($segments));
  }

  public function resolve_ref_expr(ir\nodes\RefExpr $expr): php\nodes\Expr {
    $symbol = $this->name_to_symbol->get($expr->ref->tail_segment);
    if ($symbol instanceof ir\names\VarSymbol) {
      $value = $this->renamed_symbols->get($symbol);
      return new php\nodes\VariableExpr(
        new php\nodes\Variable($value));
    }

    $segments = [ $this->renamed_symbols->get($symbol) ];
    while ($symbol = $symbol->parent) {
      array_unshift($segments, $this->renamed_symbols->get($symbol));
    }
    return new php\nodes\ReferenceExpr(
      new php\nodes\Reference($segments));
  }

  public function tmp_var(): php\nodes\Variable {
    return new php\nodes\Variable($this->allocate_tmp());
  }

  protected function var_from_name(ir\nodes\Name $name): php\nodes\Variable {
    $symbol = $this->name_to_symbol->get($name);
    $value = $this->allocate($symbol, $name);
    return new php\nodes\Variable($value);
  }

  protected function name_from_name(ir\nodes\Name $name): php\nodes\Name {
    $symbol = $this->name_to_symbol->get($name);
    $value = $this->allocate($symbol, $name);
    return new php\nodes\Name($value);
  }

  protected function ref_from_name(ir\nodes\Name $name): php\nodes\Reference {
    $symbol = $this->name_to_symbol->get($name);
    $segments = [ $this->allocate($symbol, $name) ];
    while ($symbol = $symbol->parent) {
      array_unshift($segments, $this->renamed_symbols->get($symbol));
    }
    return new php\nodes\Reference($segments);
  }

  protected function allocate(ir\names\Symbol $symbol, ir\nodes\Name $name): string {
    $candidate = $name->value;
    $counter = 0;
    $current_scope = $this->current_scope();
    while ($this->is_name_in_use($candidate, $current_scope)) {
      if ($counter === 0) {
        $candidate = "_$name->value";
      } else {
        $candidate = $name->value . "_$counter";
      }
      $counter++;
    }
    $current_scope->use_name($candidate);
    $this->renamed_symbols->set($symbol, $candidate);
    return $candidate;
  }

  protected function allocate_tmp(): string {
    $current_scope = $this->current_scope();
    while (true) {
      $candidate = $current_scope->next_tmp_name();
      if ($this->is_name_in_use($candidate, $current_scope)) {
        continue;
      } else {
        $current_scope->use_name($candidate);
        return $candidate;
      }
    }
  }

  protected function current_scope(): Scope {
    if ($scope = $this->current_function_scope()) {
      return $scope;
    } else if ($scope = $this->current_namespace_scope()) {
      return $scope;
    } else {
      return $this->root_scope;
    }
  }

  protected function is_name_in_use(string $name, Scope $scope): bool {
    return (
      $this->is_reserved($name) ||
      $scope->has_name($name)
    );
  }

  protected function is_reserved(string $name): bool {
    return in_array(strtolower($name), Reserved::WORDS);
  }
}
