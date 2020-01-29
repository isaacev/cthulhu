<?php

/**
 * @noinspection PhpUnusedLocalVariableInspection
 */

namespace Cthulhu\ast;

use Cthulhu\ast\tokens\BooleanToken;
use Cthulhu\ast\tokens\FloatToken;
use Cthulhu\ast\tokens\IntegerToken;
use Cthulhu\ast\tokens\PunctToken;
use Cthulhu\ast\tokens\StringToken;
use Cthulhu\err\Error;
use Cthulhu\ir\names\Binding;
use Cthulhu\ir\names\OperatorBinding;
use Cthulhu\ir\names\RefSymbol;
use Cthulhu\ir\names\Scope;
use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\names\TypeSymbol;
use Cthulhu\ir\names\VarSymbol;
use Cthulhu\lib\trees\Visitor;
use Cthulhu\loc\Point;
use Cthulhu\loc\Span;
use Cthulhu\val\BooleanValue;
use Cthulhu\val\FloatValue;
use Cthulhu\val\IntegerValue;
use Cthulhu\val\StringValue;
use Cthulhu\val\UnknownEscapeChar;

class DeepParser extends AbstractParser {
  public nodes\ShallowProgram $prog;

  /* @var Scope[] $namespaces */
  public array $namespaces = [];

  public Scope $root_scope;

  /* @var Scope[] $modules */
  public array $module_scopes = [];

  /* @var Scope[] $functions */
  public array $func_scopes = [];

  /* @var Scope[] $param_scopes */
  public array $param_scopes = [];

  /* @var Scope[] $block_scopes */
  public array $block_scopes = [];

  /* @var Trie[] $infix_precedence */
  public array $infix_precedence = [];

  /* @var Trie[] $prefix_precedence */
  public array $prefix_precedence = [];

  public function __construct(nodes\ShallowProgram $prog) {
    $this->prog       = $prog;
    $this->root_scope = new Scope();
  }

  private function make_ref_symbol_for_name(nodes\Name $node, ?RefSymbol $parent): RefSymbol {
    $symbol = new RefSymbol($parent);
    $this->set_symbol($node, $symbol);
    $symbol->set('node', $node);
    $symbol->set('text', $node->value);
    return $symbol;
  }

  private function make_var_symbol(nodes\LowerName $node): VarSymbol {
    $symbol = new VarSymbol();
    $this->set_symbol($node, $symbol);
    $symbol->set('node', $node);
    $symbol->set('text', $node->value);
    return $symbol;
  }

  private function make_type_symbol(nodes\TypeParamNote $node): TypeSymbol {
    $symbol = new TypeSymbol();
    $node->set('symbol', $symbol);
    $symbol->set('node', $node);
    $symbol->set('text', "'" . $node->name);
    return $symbol;
  }

  private function set_symbol(nodes\Name $node, Symbol $symbol): void {
    $node->set('symbol', $symbol);
  }

  private function current_module_scope(): Scope {
    return end($this->module_scopes);
  }

  private function push_module_scope(Scope $scope): void {
    array_push($this->module_scopes, $scope);
  }

  private function has_func_scope(): bool {
    return !empty($this->func_scopes);
  }

  private function current_func_scope(): Scope {
    return end($this->func_scopes);
  }

  private function pop_module_scope(): Scope {
    return array_pop($this->module_scopes);
  }

  private function push_func_scope(Scope $scope): void {
    array_push($this->func_scopes, $scope);
  }

  private function pop_func_scope(): Scope {
    return array_pop($this->func_scopes);
  }

  private function current_param_scope(): Scope {
    return end($this->param_scopes);
  }

  private function push_param_scope(Scope $scope): void {
    array_push($this->param_scopes, $scope);
  }

  private function pop_param_scope(): Scope {
    return array_pop($this->param_scopes);
  }

  private function has_block_scope(): bool {
    return !empty($this->block_scopes);
  }

  private function current_block_scope(): Scope {
    return end($this->block_scopes);
  }

  private function push_block_scope(Scope $scope): void {
    array_push($this->block_scopes, $scope);
  }

  private function pop_block_scope(): Scope {
    return array_pop($this->block_scopes);
  }

  private function add_namespace(Symbol $symbol, Scope $namespace): void {
    $this->namespaces[$symbol->get_id()] = $namespace;
  }

  private function get_namespace(Symbol $symbol): ?Scope {
    if (array_key_exists($symbol->get_id(), $this->namespaces)) {
      return $this->namespaces[$symbol->get_id()];
    } else {
      return null;
    }
  }

  /**
   * @return nodes\Program
   * @throws Error
   */
  public function program(): nodes\Program {
    $files = [];
    foreach ($this->prog->files as $file) {
      $files[] = $this->file($file);
    }
    return (new nodes\Program($files))
      ->set('span', $this->prog->get('span'));
  }

  /**
   * @param nodes\ShallowFile $file
   * @return nodes\File
   * @throws Error
   */
  private function file(nodes\ShallowFile $file): nodes\File {
    $file_symbol = $file->name->get('symbol');
    assert($file_symbol instanceof Symbol);

    $file_scope = $file_symbol->get('scope');
    assert($file_scope instanceof Scope);

    $this->add_namespace($file_symbol, $file_scope);
    $this->push_module_scope($file_scope);

    $file = (new nodes\File($file->name, $this->items($file->items)))
      ->set('span', $file->get('span'));

    $this->pop_module_scope();

    return $file;
  }

  /**
   * @param nodes\ShallowItem[] $items
   * @return nodes\Item[]
   * @throws Error
   */
  private function items(array $items): array {
    $new_items = [];
    foreach ($items as $item) {
      $new_items[] = $this->item($item);
    }
    return $new_items;
  }

  /**
   * @param nodes\ShallowItem $item
   * @return nodes\Item
   * @throws Error
   */
  private function item(nodes\ShallowItem $item): nodes\Item {
    switch (true) {
      case $item instanceof nodes\ShallowEnumItem:
        return $this->enum_item($item);
      case $item instanceof nodes\ShallowIntrinsicItem:
        return $this->intrinsic_item($item);
      case $item instanceof nodes\ShallowUseItem:
        return $this->use_item($item);
      case $item instanceof nodes\ShallowModItem:
        return $this->mod_item($item);
      case $item instanceof nodes\ShallowFnItem:
        return $this->fn_item($item);
      default:
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  /**
   * @param nodes\ShallowEnumItem $item
   * @return nodes\EnumItem
   * @throws Error
   */
  private function enum_item(nodes\ShallowEnumItem $item): nodes\EnumItem {
    $enum_symbol = $item->name->get('symbol');
    assert($enum_symbol instanceof Symbol);
    $this->add_namespace($enum_symbol, $form_scope = new Scope());
    $this->push_param_scope($param_scope = new Scope());

    foreach ($item->params as $note) {
      if ($param_scope->has_name($note->name)) {
        throw Errors::duplicate_enum_param($note->get('span'), $note->name);
      } else {
        $type_symbol  = $this->make_type_symbol($note);
        $type_binding = new Binding($note->name, $type_symbol, false);
        $param_scope->add_binding($type_binding);
      }
    }

    foreach ($item->forms as $form) {
      $form_symbol  = $this->make_ref_symbol_for_name($form->name, $enum_symbol);
      $form_binding = new Binding($form->name->value, $form_symbol, true);
      $form_scope->add_binding($form_binding);

      if ($form instanceof nodes\NamedFormDecl) {
        $this->add_namespace($form_symbol, $field_scope = new Scope());
        foreach ($form->params as $pair) {
          $pair_symbol  = $this->make_ref_symbol_for_name($pair->name, $form_symbol);
          $pair_binding = new Binding($pair->name->value, $pair_symbol, true);
          $field_scope->add_binding($pair_binding);
          $this->resolve_note($pair->note);
        }
      } else if ($form instanceof nodes\OrderedFormDecl) {
        foreach ($form->params as $note) {
          $this->resolve_note($note);
        }
      }
    }

    $this->pop_param_scope();

    return (new nodes\EnumItem($item->name, $item->params, $item->forms))
      ->set('pub', $item->get('pub'))
      ->set('span', $item->get('span'))
      ->set('attrs', $item->get('attrs'));
  }

  /**
   * @param nodes\ShallowIntrinsicItem $item
   * @return nodes\IntrinsicItem
   * @throws Error
   */
  private function intrinsic_item(nodes\ShallowIntrinsicItem $item): nodes\IntrinsicItem {
    foreach ($item->signatures as $signature) {
      $this->push_param_scope(new Scope());
      $this->bind_type_params($signature->params);
      $this->resolve_note($signature->params);
      $this->resolve_note($signature->returns);
      $this->pop_param_scope();
    }

    return (new nodes\IntrinsicItem($item->signatures))
      ->set('pub', $item->get('pub'))
      ->set('span', $item->get('span'))
      ->set('attrs', $item->get('attrs'));
  }

  private function use_item(nodes\ShallowUseItem $item): nodes\UseItem {
    return (new nodes\UseItem($item->path))
      ->set('pub', $item->get('pub'))
      ->set('span', $item->get('span'))
      ->set('attrs', $item->get('attrs'));
  }

  /**
   * @param nodes\ShallowModItem $item
   * @return nodes\ModItem
   * @throws Error
   */
  private function mod_item(nodes\ShallowModItem $item): nodes\ModItem {
    $mod_symbol = $item->name->get('symbol');
    assert($mod_symbol instanceof Symbol);

    $mod_scope = $mod_symbol->get('scope');
    assert($mod_scope instanceof Scope);

    $this->add_namespace($mod_symbol, $mod_scope);
    $this->push_module_scope($mod_scope);

    $item = (new nodes\ModItem($item->name, $this->items($item->items)))
      ->set('pub', $item->get('pub'))
      ->set('span', $item->get('span'))
      ->set('attrs', $item->get('attrs'));

    $this->pop_module_scope();

    return $item;
  }

  /**
   * @param nodes\ShallowFnItem $item
   * @return nodes\FnItem
   * @throws Error
   */
  private function fn_item(nodes\ShallowFnItem $item): nodes\FnItem {
    $this->push_func_scope(new Scope());
    $this->push_param_scope(new Scope());
    $this->resolve_fn_params($item->params);
    $this->resolve_note($item->returns);

    $body = $this->fn_body($item->body);

    $item = (new nodes\FnItem($item->name, $item->params, $item->returns, $body))
      ->set('pub', $item->get('pub'))
      ->set('span', $item->get('span'))
      ->set('attrs', $item->get('attrs'));

    $this->pop_param_scope();
    $this->pop_func_scope();

    return $item;
  }

  /**
   * @param nodes\FnParams $params
   * @throws Error
   */
  private function resolve_fn_params(nodes\FnParams $params) {
    foreach ($params->params as $param) {
      $this->resolve_fn_param($param);
    }
  }

  /**
   * @param nodes\ParamNode $param
   * @throws Error
   */
  private function resolve_fn_param(nodes\ParamNode $param): void {
    $name_symbol  = $this->make_var_symbol($param->name);
    $name_binding = new Binding($param->name->value, $name_symbol, false);
    $this->current_func_scope()->add_binding($name_binding);
    $this->bind_type_params($param->note);
    $this->resolve_note($param->note);
  }

  private function bind_type_params(nodes\Note $note): void {
    $param_scope = $this->current_param_scope();
    $self        = $this;

    Visitor::walk($note, [
      'TypeParamNote' => function (nodes\TypeParamNote $note) use ($param_scope, &$self): void {
        if ($param_scope->has_name($note->name) === false) {
          $type_symbol  = $self->make_type_symbol($note);
          $type_binding = new Binding($note->name, $type_symbol, false);
          $param_scope->add_binding($type_binding);
        }
      },
    ]);
  }

  /**
   * @param nodes\Note $note
   * @throws Error
   */
  private function resolve_note(nodes\Note $note): void {
    switch (true) {
      case $note instanceof nodes\FuncNote:
        $this->resolve_func_note($note);
        break;
      case $note instanceof nodes\TupleNote:
        $this->resolve_tuple_note($note);
        break;
      case $note instanceof nodes\GroupedNote:
        $this->resolve_grouped_note($note);
        break;
      case $note instanceof nodes\NamedNote:
        $this->resolve_named_note($note);
        break;
      case $note instanceof nodes\ParameterizedNote:
        $this->parameterized_note($note);
        break;
      case $note instanceof nodes\ListNote:
        $this->resolve_list_note($note);
        break;
      case $note instanceof nodes\TypeParamNote:
        $this->resolve_param_note($note);
        break;
      case $note instanceof nodes\UnitNote:
        $this->resolve_unit_note($note);
        break;
      default:
        echo get_class($note) . PHP_EOL;
        die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
    }
  }

  /**
   * @param nodes\FuncNote $note
   * @throws Error
   */
  private function resolve_func_note(nodes\FuncNote $note): void {
    $this->resolve_note($note->input);
    $this->resolve_note($note->output);
  }

  /**
   * @param nodes\TupleNote $note
   * @throws Error
   */
  private function resolve_tuple_note(nodes\TupleNote $note): void {
    foreach ($note->members as $member) {
      $this->resolve_note($member);
    }
  }

  /**
   * @param nodes\GroupedNote $note
   * @throws Error
   */
  private function resolve_grouped_note(nodes\GroupedNote $note): void {
    $this->resolve_note($note->inner);
  }

  /**
   * @param nodes\NamedNote $note
   * @throws Error
   */
  private function resolve_named_note(nodes\NamedNote $note): void {
    $this->resolve_path($note->path);
  }

  /**
   * @param nodes\ParameterizedNote $note
   * @throws Error
   */
  private function parameterized_note(nodes\ParameterizedNote $note): void {
    $this->resolve_note($note->inner);
    foreach ($note->params as $param) {
      $this->resolve_note($param);
    }
  }

  /**
   * @param nodes\ListNote $note
   * @throws Error
   */
  private function resolve_list_note(nodes\ListNote $note): void {
    $this->resolve_note($note->elements);
  }

  /**
   * @param nodes\TypeParamNote $note
   * @throws Error
   */
  private function resolve_param_note(nodes\TypeParamNote $note): void {
    $param_scope = $this->current_param_scope();
    if ($binding = $param_scope->get_name($note->name)) {
      $note->set('symbol', $binding->symbol);
    } else {
      throw Errors::unknown_type_param($note->get('span'), $note);
    }
  }

  private function resolve_unit_note(nodes\UnitNote $note): void {
    // do nothing
  }

  /**
   * @param nodes\PathNode $path
   * @throws Error
   */
  private function resolve_path(nodes\PathNode $path): void {
    // True iff the reference only has one segment and is not external
    $is_nearby = $path->is_extern === false && empty($path->head);

    if ($is_nearby) {
      $tail_name = $path->tail->value;
      if ($this->has_block_scope()) {
        // If the reference exists inside of 1 or more block scopes, explore all
        // available block scopes to see if one of them contains the name. If
        // none of the block scopes have the name, check the most recent func
        // scope in case the name was a function parameter.

        /* @var Scope[] $scopes */
        $scopes = array_merge(
          array_reverse($this->block_scopes),
          [ $this->current_func_scope() ]
        );
        foreach ($scopes as $scope) {
          if ($tail_binding = $scope->get_name($tail_name)) {
            $this->set_symbol($path->tail, $tail_binding->symbol);
            return;
          }
        }
      }

      if ($this->has_func_scope()) {
        if ($tail_binding = $this->current_func_scope()->get_name($tail_name)) {
          // If the reference exists inside of a function signature or if the
          // function body does not contain the name.
          $this->set_symbol($path->tail, $tail_binding->symbol);
          return;
        }
      }

      if ($tail_binding = $this->current_module_scope()->get_name($tail_name)) {
        // If the reference exists outside of a block scope or if none of the
        // current blocks scopes contain the name, try looking in the closest
        // module scope.
        $this->set_symbol($path->tail, $tail_binding->symbol);
        return;
      }

      throw Errors::unknown_name($path->tail->get('span'));
    } else {
      $current_module_scope = $this->current_module_scope();
      $scope                = $path->is_extern
        ? $this->root_scope
        : $this->current_module_scope();

      foreach ($path->head as $head_segment) {
        $binding = ($scope === $current_module_scope)
          ? $scope->get_name($head_segment->value)
          : $scope->get_public_name($head_segment->value);

        if ($binding) {
          $this->set_symbol($head_segment, $binding->symbol);
          if ($next_scope = $this->get_namespace($binding->symbol)) {
            $scope = $next_scope;
            continue;
          }
        }

        throw Errors::unknown_namespace_field($head_segment->get('span'));
      }

      $binding = ($scope === $current_module_scope)
        ? $scope->get_name($path->tail->value)
        : $scope->get_public_name($path->tail->value);

      if ($binding) {
        $this->set_symbol($path->tail, $binding->symbol);
      } else {
        throw Errors::unknown_namespace_field($path->tail->get('span'));
      }
    }
  }

  private function push_precedence_tries(): void {
    $mod_scope   = $this->current_module_scope();
    $infix_trie  = new Trie();
    $prefix_trie = new Trie();
    foreach ($mod_scope->get_any_bindings() as $binding) {
      if ($binding instanceof OperatorBinding) {
        switch ($binding->operator->min_arity) {
          case 2:
            $infix_trie->write_or_create($binding->name, $binding->operator);
            break;
          case 1:
            $prefix_trie->write_or_create($binding->name, $binding->operator);
            break;
        }
      }
    }
    array_push($this->infix_precedence, $infix_trie);
    array_push($this->prefix_precedence, $prefix_trie);
  }

  private function current_infix_precedence_trie(): Trie {
    return end($this->infix_precedence);
  }

  private function current_prefix_precedence_trie(): Trie {
    return end($this->prefix_precedence);
  }

  private function pop_precedence_tries(): void {
    array_pop($this->infix_precedence);
    array_pop($this->prefix_precedence);
  }

  /**
   * @return nodes\BlockNode
   * @throws Error
   */
  private function block(): nodes\BlockNode {
    $enter_block = $this->next_group_matches('{}');
    $stmts       = $this->stmts();
    $exit_block  = $this->exit_group_matches('{}');
    $span        = Span::join($enter_block, $exit_block);
    return (new nodes\BlockNode($stmts))
      ->set('span', $span);
  }

  /**
   * @param nodes\ShallowBlock $block
   * @return nodes\BlockNode
   * @throws Error
   */
  private function fn_body(nodes\ShallowBlock $block): nodes\BlockNode {
    $this->begin_parsing($block->group);
    $this->push_precedence_tries();
    $this->push_block_scope(new Scope());
    $stmts = $this->stmts();
    $this->pop_block_scope();
    $this->pop_precedence_tries();

    return (new nodes\BlockNode($stmts))
      ->set('span', $block->get('span'));
  }

  /**
   * @return nodes\Stmt[]
   * @throws Error
   */
  private function stmts(): array {
    $stmts = [];
    while (true) {
      if ($this->peek_token() === null && $this->peek_group() === null) {
        break;
      } else if (($stmts[] = $this->stmt()) instanceof nodes\ExprStmt) {
        break;
      }
    }
    return $stmts;
  }

  /**
   * @return nodes\Stmt
   * @throws Error
   */
  private function stmt(): nodes\Stmt {
    switch (true) {
      case $this->ahead_is_keyword('let'):
        return $this->let_stmt();
      default:
        return $this->expr_stmt();
    }
  }

  /**
   * @return nodes\LetStmt
   * @throws Error
   */
  private function let_stmt(): nodes\LetStmt {
    $keyword = $this->next_keyword('let');
    $name    = $this->next_lower_name();

    $block_scope = $this->current_block_scope();
    $let_symbol  = $this->make_var_symbol($name);
    $let_binding = new Binding($name->value, $let_symbol, false);
    $block_scope->add_binding($let_binding);

    $note = null;
    if ($this->ahead_is_punct(':')) {
      $colon = $this->next_punct(':');
      $note  = $this->note();
    }

    $equals = $this->next_punct('=');
    $expr   = $this->expr();
    $semi   = $this->next_punct_span(';');
    $span   = Span::join($keyword, $semi);

    return (new nodes\LetStmt($name, $note, $expr))
      ->set('span', $span);
  }

  /**
   * @return nodes\Stmt
   * @throws Error
   */
  private function expr_stmt(): nodes\Stmt {
    $expr = $this->expr();
    if ($this->ahead_is_punct(';')) {
      $semi = $this->next_punct_span(';');
      $span = Span::join($expr->get('span'), $semi);
      return (new nodes\SemiStmt($expr))
        ->set('span', $span);
    } else {
      return (new nodes\ExprStmt($expr))
        ->set('span', $expr->get('span'));
    }
  }

  /**
   * @param int $threshold
   * @return nodes\Expr
   * @throws Error
   */
  private function expr(int $threshold = Precedence::LOWEST): nodes\Expr {
    $prefix = $this->prefix_expr();
    while ($threshold < $this->next_infix_precedence()) {
      $prefix = $this->postfix_expr($prefix);
    }
    return $prefix;
  }

  private function next_infix_precedence(): int {
    if ($this->ahead_is_group('()')) {
      return Precedence::ACCESS;
    }

    if ($maybe_operator = $this->peek_infix_operator()) {
      return $maybe_operator->precedence;
    }

    return Precedence::LOWEST;
  }

  private function peek_operator(Trie $trie): ?nodes\Operator {
    $peek   = $this->peek_token();
    $best   = null;
    $tokens = [];
    while ($peek instanceof PunctToken) {
      $tokens[] = $peek;
      $char     = $peek->lexeme;
      $trie     = $trie->next($char);
      if ($trie === null) {
        break;
      } else if ($trie->value !== null) {
        $best = $trie->value;
        assert($best instanceof nodes\Operator);
      }
      if ($peek->is_joint) {
        $peek_tokens = $this->peek_tokens(count($tokens) + 1);
        if ($peek_tokens === null) {
          break;
        } else {
          $peek = end($peek_tokens);
          continue;
        }
      } else {
        break;
      }
    }
    return $best;
  }

  private function peek_infix_operator(): ?nodes\Operator {
    $trie = $this->current_infix_precedence_trie();
    return $this->peek_operator($trie);
  }

  private function peek_prefix_operator(): ?nodes\Operator {
    $trie = $this->current_prefix_precedence_trie();
    return $this->peek_operator($trie);
  }

  /**
   * @return nodes\Operator
   * @throws Error
   */
  private function next_prefix_operator(): nodes\Operator {
    $operator = $this->peek_prefix_operator();
    assert($operator instanceof nodes\Operator);
    $span = $this->next_punct_span($operator->value);
    return $operator->duplicate()
      ->copy($operator)
      ->set('span', $span);
  }

  /**
   * @return nodes\Expr
   * @throws Error
   */
  private function prefix_expr(): nodes\Expr {
    switch (true) {
      case $this->ahead_is_keyword('match'):
        return $this->match_expr();
      case $this->ahead_is_keyword('if'):
        return $this->if_expr();
      case $this->ahead_is_group('{}'):
        // closure definition
        die('unimplemented at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
      case $this->ahead_is_group('[]'):
        return $this->list_expr();
      case $this->ahead_is_group('()'):
        return $this->paren_expr();
      case $this->ahead_is_ident():
        return $this->path_expr();
      case $this->ahead_is_literal():
        return $this->literal_expr();
      default:
        if ($oper = $this->peek_prefix_operator()) {
          return $this->unary_prefix_expr();
        } else {
          $span = $this->peek_token() ?? $this->end_of_current_group();
          throw Errors::expected_expression($span);
        }
    }
  }

  /**
   * @return nodes\Expr
   * @throws Error
   */
  private function unary_prefix_expr(): nodes\Expr {
    $oper = $this->next_prefix_operator();
    $ref  = (new nodes\OperatorRef($oper))->set('span', $oper->get('span'));
    $expr = $this->expr(Precedence::UNARY);
    $span = Span::join($ref->get('span'), $expr->get('span'));
    return (new nodes\UnaryExpr($ref, $expr))
      ->set('span', $span);
  }

  /**
   * @return nodes\MatchExpr
   * @throws Error
   */
  private function match_expr(): nodes\MatchExpr {
    $keyword      = $this->next_keyword('match');
    $discriminant = $this->expr();
    $enter_arms   = $this->next_group_matches('{}');
    $arms         = $this->one_or_more_match_arms();
    $exit_arms    = $this->exit_group_matches('{}');
    $span         = Span::join($keyword, $exit_arms);
    return (new nodes\MatchExpr($discriminant, $arms))
      ->set('span', $span);
  }

  /**
   * @return nodes\MatchArm[]
   * @throws Error
   */
  private function one_or_more_match_arms(): array {
    $arms = [ $this->match_arm() ];
    while ($this->peek_token()) {
      $arms[] = $this->match_arm();
    }
    return $arms;
  }

  /**
   * @return nodes\MatchArm
   * @throws Error
   */
  private function match_arm(): nodes\MatchArm {
    $this->push_block_scope(new Scope());

    $pattern = $this->pattern();
    $arrow   = $this->next_punct('=>');
    if ($this->ahead_is_group('{}')) {
      $handler = $this->block();
    } else {
      $handler = $this->expr();
    }
    $comma = $this->next_punct_span(',');
    $span  = Span::join($pattern->get('span'), $comma);

    $this->pop_block_scope();

    return (new nodes\MatchArm($pattern, $handler))
      ->set('span', $span);
  }

  /**
   * @return nodes\Pattern
   * @throws Error
   */
  private function pattern(): nodes\Pattern {
    switch (true) {
      case $this->ahead_is_upper_ident():
        return $this->form_pattern();
      case $this->ahead_is_lower_ident():
        return $this->variable_pattern();
      case $this->ahead_is_literal():
        return $this->const_pattern();
      case $this->ahead_is_punct('_'):
        return $this->wildcard_pattern();
      default:
        throw Errors::expected_pattern($this->next_token());
    }
  }

  /**
   * @return nodes\FormPattern
   * @throws Error
   */
  private function form_pattern(): nodes\FormPattern {
    $path = $this->upper_path();
    $this->resolve_path($path);
    if ($this->ahead_is_group('{}')) {
      return $this->named_form_pattern($path);
    } else if ($this->ahead_is_group('()')) {
      return $this->ordered_form_pattern($path);
    } else {
      return $this->nullary_form_pattern($path);
    }
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\NamedFormPattern
   * @throws Error
   */
  private function named_form_pattern(nodes\PathNode $path): nodes\NamedFormPattern {
    $namespace   = $this->get_namespace($path->tail->get('symbol'));
    $enter_group = $this->next_group_matches('{}');

    /* @var nodes\NamePatternPair[] $pairs */
    $pairs      = [ $pair = $this->name_pattern_pair($namespace) ];
    $names_used = [ $pair->name->value ];
    while ($this->ahead_is_punct(',')) {
      $comma   = $this->next_punct(',');
      $pairs[] = $pair = $this->name_pattern_pair($namespace);
      if (in_array($pair->name->value, $names_used)) {
        throw Errors::duplicate_field_binding($pair);
      } else {
        $names_used[] = $pair->name->value;
      }
    }

    $exit_group = $this->exit_group_matches('{}');
    $span       = Span::join($path->get('span'), $exit_group);

    foreach ($namespace->get_any_bindings() as $name => $binding) {
      if (in_array($name, $names_used) === false) {
        throw Errors::missing_field_binding($span, $name);
      }
    }

    return (new nodes\NamedFormPattern($path, $pairs))
      ->set('span', $span);
  }

  /**
   * @param Scope $namespace
   * @return nodes\NamePatternPair
   * @throws Error
   */
  private function name_pattern_pair(Scope $namespace): nodes\NamePatternPair {
    $name = $this->next_lower_name();

    if ($binding = $namespace->get_name($name->value)) {
      $name->set('symbol', $binding->symbol);
    } else {
      throw Errors::unknown_form_field($name->get('span'), $name->value);
    }

    $colon   = $this->next_punct(':');
    $pattern = $this->pattern();
    $span    = Span::join($name->get('span'), $pattern->get('span'));
    return (new nodes\NamePatternPair($name, $pattern))
      ->set('span', $span);
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\OrderedFormPattern
   * @throws Error
   */
  private function ordered_form_pattern(nodes\PathNode $path): nodes\OrderedFormPattern {
    $enter_group = $this->next_group_matches('()');
    $patterns    = $this->one_or_more_patterns();
    $exit_group  = $this->exit_group_matches('()');
    $span        = Span::join($path->get('span'), $exit_group);
    return (new nodes\OrderedFormPattern($path, $patterns))
      ->set('span', $span);
  }

  /**
   * @return nodes\Pattern[]
   * @throws Error
   */
  private function one_or_more_patterns(): array {
    $patterns = [ $this->pattern() ];
    while ($this->ahead_is_punct(',')) {
      $comma      = $this->next_punct(',');
      $patterns[] = $this->pattern();
    }
    return $patterns;
  }

  private function nullary_form_pattern(nodes\PathNode $path): nodes\NullaryFormPattern {
    $span = $path->get('span');
    return (new nodes\NullaryFormPattern($path))
      ->set('span', $span);
  }

  /**
   * @return nodes\VariablePattern
   * @throws Error
   */
  private function variable_pattern(): nodes\VariablePattern {
    $name    = $this->next_lower_name();
    $symbol  = $this->make_var_symbol($name);
    $binding = new Binding($name->value, $symbol, false);
    $this->current_block_scope()->add_binding($binding);
    return (new nodes\VariablePattern($name))
      ->set('span', $name->get('span'));
  }

  /**
   * @return nodes\ConstPattern
   * @throws Error
   */
  private function const_pattern(): nodes\ConstPattern {
    $const = $this->literal_expr();
    return (new nodes\ConstPattern($const))
      ->set('span', $const->get('span'));
  }

  /**
   * @return nodes\WildcardPattern
   * @throws Error
   */
  private function wildcard_pattern(): nodes\WildcardPattern {
    $span = $this->next_punct_span('_');
    return (new nodes\WildcardPattern())
      ->set('span', $span);
  }

  /**
   * @return nodes\IfExpr
   * @throws Error
   */
  private function if_expr(): nodes\IfExpr {
    $if         = $this->next_keyword('if');
    $condition  = $this->expr();
    $consequent = $this->block();

    if ($this->ahead_is_keyword('else')) {
      $else      = $this->next_keyword('else');
      $alternate = $this->block();
    } else {
      $alternate = null;
    }

    $span = Span::join($if, ($alternate ?? $consequent)->get('span'));
    return (new nodes\IfExpr($condition, $consequent, $alternate))
      ->set('span', $span);
  }

  /**
   * @return nodes\ListExpr
   * @throws Error
   */
  private function list_expr(): nodes\ListExpr {
    $enter_bracket = $this->next_group_matches('[]');
    $elements      = $this->zero_or_more_exprs();
    $exit_bracket  = $this->exit_group_matches('[]');
    $span          = Span::join($enter_bracket, $exit_bracket);
    return (new nodes\ListExpr($elements))
      ->set('span', $span);
  }

  /**
   * @return nodes\Expr
   * @throws Error
   */
  private function paren_expr(): nodes\Expr {
    $group = $this->next_group_matches('()');
    if ($this->peek_token() === null) {
      $this->exit_group_matches('()');
      return (new nodes\UnitLiteral())
        ->set('span', $group);
    } else {
      $expr = $this->expr();
      $this->exit_group_matches('()');
      return $expr;
    }
  }

  /**
   * @return nodes\Expr
   * @throws Error
   */
  private function path_expr(): nodes\Expr {
    $path = $this->path();
    $this->resolve_path($path);
    if ($path->tail instanceof nodes\UpperName) {
      return $this->constructor_expr($path);
    } else {
      return (new nodes\PathExpr($path))
        ->set('span', $path->get('span'));
    }
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantConstructorExpr
   * @throws Error
   */
  private function constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    if ($this->ahead_is_group('{}')) {
      return $this->named_constructor_expr($path);
    } else if ($this->ahead_is_group('()')) {
      return $this->ordered_constructor_expr($path);
    } else {
      return $this->unit_constructor_expr($path);
    }
  }

  /**
   * @return nodes\FieldExprNode
   * @throws Error
   */
  private function named_expr(): nodes\FieldExprNode {
    $name  = $this->next_lower_name();
    $colon = $this->next_punct(':');
    $expr  = $this->expr();
    $span  = Span::join($name->get('span'), $expr->get('span'));
    return (new nodes\FieldExprNode($name, $expr))
      ->set('span', $span);
  }

  /**
   * @return nodes\FieldExprNode[]
   * @throws Error
   */
  private function one_or_more_named_exprs(): array {
    $fields = [ $this->named_expr() ];
    while ($this->ahead_is_punct(',')) {
      $comma    = $this->next_punct(',');
      $fields[] = $this->named_expr();
    }
    return $fields;
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantConstructorExpr
   * @throws Error
   */
  private function named_constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    $group = $this->next_group_matches('{}');
    $pairs = $this->one_or_more_named_exprs();
    $this->exit_group_matches('{}');

    $span   = $group->span();
    $fields = (new nodes\NamedVariantConstructorFields($pairs))
      ->set('span', $span);

    $span = Span::join($path->get('span'), $span);
    return (new nodes\VariantConstructorExpr($path, $fields))
      ->set('span', $span);
  }

  /**
   * @return nodes\Expr[]
   * @throws Error
   */
  protected function zero_or_more_exprs(): array {
    if ($this->peek_token() === null) {
      return [];
    }
    return $this->one_or_more_exprs();
  }

  /**
   * @return nodes\Expr[]
   * @throws Error
   */
  protected function one_or_more_exprs(): array {
    $exprs = [ $this->expr() ];
    while ($this->ahead_is_punct(',')) {
      $comma   = $this->next_punct(',');
      $exprs[] = $this->expr();
    }
    return $exprs;
  }

  /**
   * @param nodes\PathNode $path
   * @return nodes\VariantConstructorExpr
   * @throws Error
   */
  private function ordered_constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    $group = $this->next_group_matches('()');
    $order = $this->one_or_more_exprs();
    $this->exit_group_matches('()');

    $span   = $group->span();
    $fields = (new nodes\OrderedVariantConstructorFields($order))
      ->set('span', $span);

    $span = Span::join($path->get('span'), $span);
    return (new nodes\VariantConstructorExpr($path, $fields))
      ->set('span', $span);
  }

  private function unit_constructor_expr(nodes\PathNode $path): nodes\VariantConstructorExpr {
    return (new nodes\VariantConstructorExpr($path, null))
      ->set('span', $path->get('span'));
  }

  /**
   * @return nodes\Literal
   * @throws Error
   */
  private function literal_expr(): nodes\Literal {
    $token = $this->next_literal();
    $span  = $token->span;
    if ($token instanceof StringToken) {
      try {
        $value = StringValue::from_scalar($token->lexeme);
        return (new nodes\StrLiteral($value))
          ->set('span', $span);
      } catch (UnknownEscapeChar $err) {
        $file   = $span->from->file;
        $line   = $span->from->line;
        $column = $span->from->column + $err->char_offset;
        $span   = (new Point($file, $line, $column))->span();
        throw Errors::unknown_escape_char($span);
      }
    } else if ($token instanceof FloatToken) {
      $value = new FloatValue($token->lexeme, floatval($token->lexeme), $token->precision);
      return (new nodes\FloatLiteral($value))
        ->set('span', $span);
    } else if ($token instanceof IntegerToken) {
      $value = new IntegerValue($token->lexeme, intval($token->lexeme, 10));
      return (new nodes\IntLiteral($value))
        ->set('span', $span);
    } else if ($token instanceof BooleanToken) {
      $value = BooleanValue::from_scalar($token->lexeme === 'true');
      return (new nodes\BoolLiteral($value))
        ->set('span', $span);
    }

    die('unreachable at ' . __LINE__ . ' in ' . __FILE__ . PHP_EOL);
  }

  /**
   * @return nodes\Operator
   * @throws Error
   */
  private function next_infix_operator(): nodes\Operator {
    $operator = $this->peek_infix_operator();
    assert($operator instanceof nodes\Operator);
    $this->next_punct($operator->value);
    return $operator;
  }

  /**
   * @param nodes\Expr $prefix
   * @return nodes\Expr
   * @throws Error
   */
  private function postfix_expr(nodes\Expr $prefix): nodes\Expr {
    if ($this->ahead_is_group('()')) {
      return $this->call_expr($prefix);
    } else {
      $operator = $this->next_infix_operator();
      return $this->binary_infix_expr($prefix, $operator);
    }
  }

  /**
   * @param nodes\Expr $prefix
   * @return nodes\CallExpr
   * @throws Error
   */
  private function call_expr(nodes\Expr $prefix): nodes\CallExpr {
    $enter_parens = $this->next_group_matches('()');
    $args         = $this->zero_or_more_exprs();
    $exit_parens  = $this->exit_group_matches('()');
    $args         = (new nodes\Exprs($args))->set('span', Span::join($enter_parens, $exit_parens));
    $span         = Span::join($prefix->get('span'), $args->get('span'));
    return (new nodes\CallExpr($prefix, $args))
      ->set('span', $span);
  }

  /**
   * @param nodes\Expr     $prefix
   * @param nodes\Operator $oper
   * @return nodes\BinaryExpr
   * @throws Error
   */
  private function binary_infix_expr(nodes\Expr $prefix, nodes\Operator $oper): nodes\BinaryExpr {
    $postfix = $this->expr($oper->precedence);
    $span    = Span::join($prefix->get('span'), $postfix->get('span'));
    $ref     = (new nodes\OperatorRef($oper))->set('span', $oper->get('span'));
    return (new nodes\BinaryExpr($ref, $prefix, $postfix))
      ->set('span', $span);
  }

  /**
   * @return nodes\PathNode
   * @throws Error
   */
  private function path(): nodes\PathNode {
    $is_extern = false;
    $span      = null;
    $body      = [];

    while ($this->ahead_is_upper_ident()) {
      $body[] = $this->next_upper_name();
      $span   = isset($span) ? $span : end($body)->get('span');

      if ($this->ahead_is_punct('::')) {
        $colons = $this->next_punct('::');
      } else {
        break;
      }
    }

    if ($this->ahead_is_lower_ident()) {
      $tail = $this->next_lower_name();
    } else if (empty($body)) {
      $tail = $this->next_upper_name();
    } else {
      $tail = array_pop($body);
    }

    $span = Span::join($span ?? $tail->get('span'), $tail->get('span'));
    return (new nodes\PathNode($is_extern, $body, $tail))
      ->set('span', $span);
  }
}
