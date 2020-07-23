<?php

namespace Cthulhu\php;

use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\nodes as ir;
use Cthulhu\lib\panic\Panic;
use Cthulhu\php\names\ClosureScope;
use Cthulhu\php\names\Scope;

class Names {
  private const PUNCT_TO_SAFE_CHAR = [
    '%' => 'percent',
    '&' => 'amp',
    '*' => 'star',
    '+' => 'plus',
    '-' => 'dash',
    '.' => 'dot',
    ':' => 'colon',
    '<' => 'left_angle',
    '=' => 'equals',
    '>' => 'right_angle',
    '@' => 'at',
    '^' => 'caret',
    '|' => 'pipe',
    '~' => 'tilde',
    '/' => 'slash',
  ];

  private names\Scope $root_scope;

  /* @var names\Scope[] $space_scopes */
  private array $space_scopes = [];

  /* @var names\Scope[] $func_scopes */
  private array $func_scopes = [];

  public function __construct() {
    $this->root_scope = new names\Scope();
  }

  private function current_space_scope(): ?names\Scope {
    return empty($this->space_scopes)
      ? null
      : end($this->space_scopes);
  }

  private function current_func_scope(): ?names\Scope {
    return empty($this->func_scopes)
      ? null
      : end($this->func_scopes);
  }

  private function current_scope(): names\Scope {
    if ($scope = $this->current_func_scope()) {
      return $scope;
    } else if ($scope = $this->current_space_scope()) {
      return $scope;
    } else {
      return $this->root_scope;
    }
  }

  private function operator_to_safe_text(string $operator): string {
    assert(strlen($operator) > 0);
    $chars = str_split($operator);
    $out   = '';
    foreach ($chars as $char) {
      if (array_key_exists($char, self::PUNCT_TO_SAFE_CHAR) === false) {
        Panic::with_reason(__LINE__, __FILE__, "cannot use '$char' in operator");
      } else if (empty($out)) {
        $out .= self::PUNCT_TO_SAFE_CHAR[$char];
      } else {
        $out .= '_' . self::PUNCT_TO_SAFE_CHAR[$char];
      }
    }
    return $out;
  }

  private function rename_ir_name(Symbol $ir_symbol): string {
    $candidate = $ir_text = ($ir_symbol->has('operator'))
      ? $this->operator_to_safe_text($ir_symbol->get('operator'))
      : $ir_symbol->get('text');

    $counter       = 0;
    $current_scope = $this->current_scope();
    while ($current_scope->is_name_unavailable($candidate)) {
      if ($counter === 0) {
        $candidate = "_$ir_text";
      } else {
        $candidate = "${ir_text}_$counter";
      }
      $counter++;
    }
    $current_scope->use_name($candidate);
    $ir_symbol->set('php/string', $candidate);
    return $candidate;
  }

  /**
   * @return nodes\Variable
   */
  public function tmp_var(): nodes\Variable {
    $current_scope = $this->current_scope();
    $candidate     = $current_scope->next_unused_tmp_name();
    $php_symbol    = new names\Symbol();
    return new nodes\Variable($candidate, $php_symbol);
  }

  /**
   * @param ir\Name $name
   * @return nodes\Variable
   */
  public function name_to_var(ir\Name $name): nodes\Variable {
    if ($php_var = $name->symbol->get('php/var')) {
      return $php_var;
    }

    $php_value  = $this->rename_ir_name($name->symbol);
    $php_symbol = new names\Symbol();
    $php_var    = new nodes\Variable($php_value, $php_symbol);
    $name->symbol->set('php/var', $php_var);
    return $php_var;
  }

  /**
   * @param ir\Name $tail
   * @return nodes\Reference
   */
  public function name_to_ref(ir\Name $tail): nodes\Reference {
    if ($php_ref = $tail->symbol->get('php/ref')) {
      // If this symbol has already been translated to a valid PHP identifier,
      // use the cached value instead of computing a new identifier.
      return $php_ref;
    }

    $php_values  = [ $tail->symbol->get('php/string') ?? $this->rename_ir_name($tail->symbol) ];
    $curr_symbol = $tail->symbol;
    while ($curr_symbol = $curr_symbol->parent) {
      assert($curr_symbol instanceof RefSymbol);
      $php_value = $curr_symbol->get('php/string') ?? $this->rename_ir_name($curr_symbol);
      array_unshift($php_values, $php_value);
    }

    $php_symbol = new names\Symbol();
    $php_ref    = new nodes\Reference(implode('\\', $php_values), $php_symbol);

    $tail->symbol->set('php/ref', $php_ref);
    return $php_ref;
  }

  /**
   * @param ir\Name $ir_name
   * @return nodes\Name
   */
  public function name_to_name(ir\Name $ir_name): nodes\Name {
    if ($php_name = $ir_name->get('php/name')) {
      return $php_name;
    }

    $ir_symbol  = $ir_name->symbol;
    $php_value  = $this->rename_ir_name($ir_symbol);
    $php_symbol = new names\Symbol();
    $php_name   = new nodes\Name($php_value, $php_symbol);

    $ir_name->set('php/name', $php_name);
    return $php_name;
  }

  /**
   * @param ir\Name         $ir_name
   * @param nodes\Reference $parent
   * @return nodes\Name
   */
  public function name_to_ref_name(ir\Name $ir_name, nodes\Reference $parent): nodes\Name {
    if ($php_name = $ir_name->get('php/name')) {
      return $php_name;
    }

    $ir_symbol    = $ir_name->symbol;
    $php_value    = $this->rename_ir_name($ir_symbol);
    $php_symbol   = new names\Symbol();
    $php_name     = new nodes\Name($php_value, $php_symbol);
    $php_segments = $parent->segments . '\\' . $php_value;
    $php_ref      = new nodes\Reference($php_segments, $php_symbol);

    $ir_name->set('php/name', $php_name);
    $ir_symbol->set('php/ref', $php_ref);
    return $php_name;
  }

  public function enter_closure_scope(): void {
    $scope = new ClosureScope($this->current_func_scope());
    array_push($this->func_scopes, $scope);
  }

  public function exit_closure_scope(): ClosureScope {
    assert(!empty($this->func_scopes));
    $scope = array_pop($this->func_scopes);
    assert($scope instanceof ClosureScope);
    return $scope;
  }

  public function enter_namespace_scope(): void {
    array_push($this->space_scopes, new Scope());
  }

  public function exit_namespace_scope(): Scope {
    assert(!empty($this->space_scopes));
    return array_pop($this->space_scopes);
  }

  public function enter_func_scope(): void {
    array_push($this->func_scopes, new Scope());
  }

  public function exit_func_scope(): Scope {
    assert(!empty($this->func_scopes));
    return array_pop($this->func_scopes);
  }
}
