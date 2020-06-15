<?php

/** @noinspection PhpUnusedLocalVariableInspection */

namespace Cthulhu\ast;

use Cthulhu\ast\nodes\PathNode;
use Cthulhu\ast\tokens\IdentToken;
use Cthulhu\ast\tokens\LiteralToken;
use Cthulhu\ast\tokens\PunctToken;
use Cthulhu\ast\tokens\Token;
use Cthulhu\err\Error;
use Cthulhu\loc\Span;

abstract class AbstractParser {
  public const RESERVED_WORDS = [
    'else',
    'false',
    'fn',
    'if',
    'let',
    'match',
    'mod',
    'native',
    'pub',
    'super',
    'true',
    'type',
    'unreachable',
    'use',
  ];

  /* @var TokenGroup[] $group_stack */
  protected array $group_stack = [];

  /* @var int[] $offset_stack */
  protected array $offset_stack = [];

  protected function begin_parsing(TokenGroup $group): void {
    $this->group_stack  = [ $group ];
    $this->offset_stack = [ 0 ];
  }

  private function end_of_current_group(): Span {
    return end($this->group_stack)->right;
  }

  protected function peek_span(): Span {
    if ($token = $this->peek_token()) {
      return $token->span;
    } else if ($group = $this->peek_group()) {
      return $group->from()->span();
    } else {
      return $this->end_of_current_group();
    }
  }

  /**
   * @param Token[] $tokens
   * @return string
   */
  protected function tokens_to_string(array $tokens): string {
    $out = '';
    foreach ($tokens as $token) {
      $out .= $token->lexeme;
    }
    return $out;
  }

  /**
   * Iff another token exists in the current token group, return that token but
   * do not advance the parser. Return null otherwise.
   *
   * @return Token|null
   */
  protected function peek_token(): ?Token {
    if (empty($this->group_stack)) {
      return null;
    }

    $curr_offset = end($this->offset_stack);
    $curr_group  = end($this->group_stack);
    if ($curr_offset >= count($curr_group)) {
      return null;
    }

    $curr_tree = $curr_group->members[$curr_offset];
    if ($curr_tree instanceof TokenLeaf) {
      return $curr_tree->token;
    } else {
      return null;
    }
  }

  /**
   * @return bool
   */
  protected function ahead_is_end_of_current_group(): bool {
    $curr_offset = end($this->offset_stack);
    $curr_group  = end($this->group_stack);
    return $curr_offset >= count($curr_group);
  }

  /**
   * Iff `n` tokens remain in the current token group, return that many tokens
   * but do not advance the parser. Return null if not enough tokens exist in
   * the current token group.
   *
   * @param int $n
   * @return array|null
   */
  protected function peek_tokens(int $n): ?array {
    assert($n > 0);
    if (empty($this->group_stack)) {
      return null;
    }

    $curr_offset = end($this->offset_stack);
    $peek_offset = $curr_offset + $n - 1;
    $curr_group  = end($this->group_stack);
    if ($peek_offset >= count($curr_group)) {
      return null;
    }

    $tokens = [];
    for ($i = $curr_offset; $i <= $peek_offset; $i++) {
      $peek_tree = $curr_group->members[$i];
      if ($peek_tree instanceof TokenLeaf) {
        $tokens[] = $peek_tree->token;
      } else {
        return null;
      }
    }
    return $tokens;
  }

  /**
   * @param string $pattern
   * @return bool
   */
  protected function ahead_is_punct(string $pattern): bool {
    assert($pattern !== '');

    $chars  = str_split($pattern);
    $tokens = $this->peek_tokens(strlen($pattern));
    if ($tokens === null) {
      return false;
    }

    assert(count($tokens) === count($chars));

    // Only continue if the tokens match the pattern and are contiguous
    foreach ($tokens as $index => $token) {
      $char            = $chars[$index];
      $chars_remaining = count($chars) - $index - 1;
      if (
        $token instanceof PunctToken &&
        $token->lexeme === $char &&
        ($chars_remaining > 0 ? $token->is_joint : true)
      ) {
        continue;
      } else {
        return false;
      }
    }

    return true;
  }

  /**
   * Iff another token exists in the current token group, return that token and
   * advance the parser. Return null otherwise.
   *
   * @return Token|null
   */
  protected function next_token(): ?Token {
    if (empty($this->group_stack)) {
      return null;
    }

    $curr_offset = end($this->offset_stack);
    $curr_group  = end($this->group_stack);
    if ($curr_offset >= count($curr_group)) {
      return null;
    }

    $curr_tree = $curr_group->members[$curr_offset];
    if ($curr_tree instanceof TokenLeaf) {
      array_pop($this->offset_stack);
      array_push($this->offset_stack, $curr_offset + 1);
      return $curr_tree->token;
    } else {
      return null;
    }
  }

  /**
   * @param string $pattern
   * @return PunctToken[]
   * @throws Error
   */
  protected function next_punct(string $pattern): array {
    assert($pattern !== '');

    $chars  = str_split($pattern);
    $tokens = $this->peek_tokens(strlen($pattern));
    if ($tokens === null) {
      throw Errors::expected_token($this->peek_span(), $pattern);
    }

    assert(count($tokens) === count($chars));

    // Only continue if the tokens match the pattern and are contiguous
    foreach ($tokens as $index => $token) {
      $char            = $chars[$index];
      $chars_remaining = count($chars) - $index - 1;
      if (
        $token instanceof PunctToken &&
        $token->lexeme === $char &&
        ($chars_remaining > 0 ? $token->is_joint : true)
      ) {
        continue;
      } else {
        $span = Span::join(...array_slice($tokens, 0, $index + 1));
        throw Errors::expected_token($span, $pattern);
      }
    }

    // Since the tokens matched, advance the parser
    for ($i = 0; $i < count($chars); $i++) {
      $this->next_token();
    }

    return $tokens;
  }

  protected function peek_group(): ?TokenGroup {
    if (empty($this->group_stack)) {
      return null;
    }

    $curr_offset = end($this->offset_stack);
    $curr_group  = end($this->group_stack);
    if ($curr_offset >= count($curr_group)) {
      return null;
    }

    $curr_tree = $curr_group->members[$curr_offset];
    if ($curr_tree instanceof TokenGroup) {
      return $curr_tree;
    } else {
      return null;
    }
  }

  protected function ahead_is_group(string $delim): bool {
    assert(in_array($delim, [ '{}', '[]', '()' ]));
    if ($peek = $this->peek_group()) {
      return $peek->delim === $delim;
    }
    return false;
  }

  protected function next_group(): ?TokenGroup {
    if (empty($this->group_stack)) {
      return null;
    }

    $curr_offset = end($this->offset_stack);
    $curr_group  = end($this->group_stack);
    if ($curr_offset >= count($curr_group)) {
      return null;
    }

    $curr_tree = $curr_group->members[$curr_offset];
    if ($curr_tree instanceof TokenGroup) {
      array_pop($this->offset_stack);
      array_push($this->offset_stack, $curr_offset + 1);
      return $curr_tree;
    } else {
      return null;
    }
  }

  /**
   * @param string $delim
   * @return TokenGroup
   * @throws Error
   */
  protected function next_group_matches(string $delim): TokenGroup {
    assert(in_array($delim, [ '{}', '[]', '()' ]));
    $maybe_group = $this->next_group();
    if ($maybe_group === null) {
      throw Errors::expected_token($this->peek_span(), "expected $delim[0]");
    } else if ($maybe_group->delim !== $delim) {
      throw Errors::expected_token($maybe_group->left, "expected $delim[0]");
    } else {
      array_push($this->offset_stack, 0);
      array_push($this->group_stack, $maybe_group);
      return $maybe_group;
    }
  }

  /**
   * @param string $delim
   * @return TokenGroup
   * @throws Error
   */
  protected function exit_group_matches(string $delim): TokenGroup {
    assert(in_array($delim, [ '{}', '[]', '()' ]));
    $curr_offset = end($this->offset_stack);
    $curr_group  = end($this->group_stack);
    assert($curr_group->delim === $delim);
    if ($peek = $this->peek_token()) {
      throw Errors::expected_token($peek, "expected " . $delim[1]);
    }
    array_pop($this->offset_stack);
    return array_pop($this->group_stack);
  }

  /**
   * @param string $pattern
   * @return Span|null
   * @throws Error
   */
  protected function next_punct_span(string $pattern): ?Span {
    $maybe_tokens = $this->next_punct($pattern);
    if ($maybe_tokens === null) {
      return null;
    } else {
      return Span::join(...$maybe_tokens);
    }
  }

  /**
   * @return PunctToken[]
   */
  protected function next_contiguous_punct(): array {
    $tokens   = [];
    $is_joint = true;
    while (($peek = $this->peek_token()) && $is_joint) {
      if ($peek instanceof PunctToken) {
        $tokens[] = $this->next_token();
        $is_joint = $peek->is_joint;
      } else {
        break;
      }
    }
    return $tokens;
  }

  protected function is_reserved(IdentToken $token): bool {
    return in_array($token->lexeme, self::RESERVED_WORDS);
  }

  protected function not_reserved(IdentToken $token): bool {
    return !$this->is_reserved($token);
  }

  protected function ahead_is_lower_ident(): bool {
    return (
      ($token = $this->peek_token()) &&
      $token instanceof IdentToken &&
      $token->is_lowercase() &&
      $this->not_reserved($token)
    );
  }

  protected function ahead_is_upper_ident(): bool {
    return (
      ($token = $this->peek_token()) &&
      $token instanceof IdentToken &&
      $token->is_uppercase() &&
      $this->not_reserved($token)
    );
  }

  protected function ahead_is_ident(): bool {
    return (
      ($token = $this->peek_token()) &&
      $token instanceof IdentToken &&
      $this->not_reserved($token)
    );
  }

  protected function ahead_is_reserved(): bool {
    return (
      ($token = $this->peek_token()) &&
      $token instanceof IdentToken &&
      $this->is_reserved($token)
    );
  }

  protected function ahead_is_keyword(string $keyword): bool {
    return (
      ($token = $this->peek_token()) &&
      $token instanceof IdentToken &&
      $token->is_lowercase() &&
      $this->is_reserved($token) &&
      $keyword === $token->lexeme
    );
  }

  protected function ahead_is_literal(): bool {
    return (
      ($token = $this->peek_token()) &&
      $token instanceof LiteralToken
    );
  }

  /**
   * @return LiteralToken
   * @throws Error
   */
  protected function next_literal(): LiteralToken {
    if ($token = $this->next_token()) {
      if ($token instanceof LiteralToken) {
        return $token;
      } else {
        throw Errors::expected_token($token, 'literal');
      }
    } else {
      throw Errors::expected_token($this->peek_span(), 'literal');
    }
  }

  /**
   * @return nodes\LowerName
   * @throws Error
   */
  protected function next_lower_name(): nodes\LowerName {
    if ($this->ahead_is_lower_ident()) {
      $token = $this->next_token();
      return (new nodes\LowerName($token->lexeme))
        ->set('span', $token->span);
    }

    if ($this->ahead_is_reserved()) {
      throw Errors::used_reserved_ident($this->peek_token());
    }

    throw Errors::expected_token($this->peek_span(), 'lowercase identifier');
  }

  /**
   * @return nodes\UpperName
   * @throws Error
   */
  protected function next_upper_name(): nodes\UpperName {
    if ($this->ahead_is_upper_ident()) {
      $token = $this->next_token();
      return (new nodes\UpperName($token->lexeme))
        ->set('span', $token->span);
    }

    throw Errors::expected_token($this->peek_span(), 'uppercase identifier');
  }

  /**
   * @return nodes\SuperName
   * @throws Error
   */
  protected function next_super_name(): nodes\SuperName {
    if ($this->ahead_is_keyword('super')) {
      $token = $this->next_token();
      return (new nodes\SuperName())
        ->set('span', $token->span);
    }

    throw Errors::expected_token($this->peek_span(), 'super keyword');
  }

  /**
   * @param string $keyword
   * @return IdentToken
   * @throws Error
   */
  protected function next_keyword(string $keyword): IdentToken {
    if ($token = $this->next_token()) {
      if ($token instanceof IdentToken && $token->lexeme === $keyword) {
        return $token;
      } else {
        throw Errors::expected_token($token, "keyword '$keyword'");
      }
    }

    throw Errors::expected_token($this->peek_span(), "keyword '$keyword'");
  }

  /**
   * @return nodes\LowerName[]
   * @throws Error
   */
  protected function one_or_more_comma_separated_names(): array {
    $names = [ $this->next_lower_name() ];
    while ($this->ahead_is_punct(',')) {
      $comma   = $this->next_punct(',');
      $names[] = $this->next_lower_name();
    }
    return $names;
  }

  /**
   * @return nodes\Note[]
   * @throws Error
   */
  protected function zero_or_more_notes(): array {
    // FIXME: what if next note is the start of a list type?
    if ($this->ahead_is_end_of_current_group()) {
      return [];
    }
    return $this->one_or_more_notes();
  }

  /**
   * @return nodes\Note[]
   * @throws Error
   */
  protected function one_or_more_notes(): array {
    $notes = [ $this->note() ];
    while ($this->ahead_is_punct(',')) {
      $comma   = $this->next_punct(',');
      $notes[] = $this->note();
    }
    return $notes;
  }

  /**
   * @return nodes\ShallowBlock
   * @throws Error
   */
  protected function shallow_block(): nodes\ShallowBlock {
    $enter_block = $this->next_group_matches('{}');
    $group       = array_pop($this->group_stack);
    array_pop($this->offset_stack);
    return (new nodes\ShallowBlock($group))
      ->set('span', $group->span());
  }

  /**
   * @return nodes\Note
   * @throws Error
   */
  protected function note(): nodes\Note {
    if ($this->ahead_is_punct("'")) {
      $prefix = $this->param_note();
    } else if ($this->ahead_is_ident() || $this->ahead_is_keyword('super')) {
      $prefix = $this->named_note();
    } else if ($this->ahead_is_group('()')) {
      $prefix = $this->grouped_note();
    } else if ($this->ahead_is_group('[]')) {
      $prefix = $this->list_note();
    } else if ($this->ahead_is_group('{}')) {
      $prefix = $this->record_note();
    } else {
      throw Errors::expected_note($this->peek_span());
    }

    while ($this->ahead_is_punct('->')) {
      $prefix = $this->func_note($prefix);
    }

    return $prefix;
  }

  /**
   * @return nodes\TypeParamNote
   * @throws Error
   */
  protected function param_note(): nodes\TypeParamNote {
    $quote = $this->next_punct("'")[0];
    if ($quote->is_joint === false || $this->ahead_is_lower_ident() === false) {
      throw Errors::unnamed_type_param($quote);
    }
    $name = $this->next_lower_name();
    $span = Span::join($quote, $name->get('span'));
    return (new nodes\TypeParamNote($name->value))
      ->set('span', $span);
  }

  /**
   * @return nodes\Note
   * @throws Error
   */
  protected function named_note(): nodes\Note {
    $path = $this->upper_path();
    $note = (new nodes\NamedNote($path))
      ->set('span', $path->get('span'));

    if ($this->ahead_is_group('()')) {
      $enter_args = $this->next_group_matches('()');
      $args       = $this->one_or_more_notes();
      $exit_args  = $this->exit_group_matches('()');
      $span       = Span::join($note->get('span'), $exit_args);
      return (new nodes\ParameterizedNote($note, $args))
        ->set('span', $span);
    } else {
      return $note;
    }
  }

  /**
   * @return nodes\Note
   * @throws Error
   */
  protected function grouped_note(): nodes\Note {
    $enter_note = $this->next_group_matches('()');
    $notes      = $this->zero_or_more_notes();
    $exit_note  = $this->exit_group_matches('()');
    $span       = Span::join($enter_note, $exit_note);

    switch (count($notes)) {
      case 0:
        return (new nodes\UnitNote())
          ->set('span', $span);
      case 1:
        return (new nodes\GroupedNote($notes[0]))
          ->set('span', $span);
      default:
        return (new nodes\TupleNote($notes))
          ->set('span', $span);
    }
  }

  /**
   * @return nodes\ListNote
   * @throws Error
   */
  protected function list_note(): nodes\ListNote {
    $enter_note = $this->next_group_matches('[]');
    $note       = $this->note();
    $exit_note  = $this->exit_group_matches('[]');
    $span       = Span::join($enter_note, $exit_note);
    return (new nodes\ListNote($note))
      ->set('span', $span);
  }

  /**
   * @return nodes\ParamNode
   * @throws Error
   */
  protected function name_type_pair(): nodes\ParamNode {
    $name  = $this->next_lower_name();
    $colon = $this->next_punct(':');
    $note  = $this->note();
    $span  = Span::join($name->get('span'), $note->get('span'));
    return (new nodes\ParamNode($name, $note))
      ->set('span', $span);
  }

  /**
   * @return nodes\ParamNode[]
   * @throws Error
   */
  protected function one_or_more_name_type_pairs(): array {
    $params = [ $this->name_type_pair() ];
    while ($this->ahead_is_punct(',')) {
      $comma    = $this->next_punct(',');
      $params[] = $this->name_type_pair();
    }
    return $params;
  }

  /**
   * @return nodes\ParamNode[]
   * @throws Error
   */
  protected function zero_or_more_name_type_pairs(): array {
    if ($this->peek_token() === null) {
      return [];
    }
    return $this->one_or_more_name_type_pairs();
  }

  /**
   * @return nodes\RecordNote
   * @throws Error
   */
  protected function record_note(): nodes\RecordNote {
    $enter_note = $this->next_group_matches('{}');
    $fields     = $this->one_or_more_name_type_pairs();
    $exit_note  = $this->exit_group_matches('{}');
    $span       = Span::join($enter_note, $exit_note);
    return (new nodes\RecordNote($fields))
      ->set('span', $span);
  }

  /**
   * @param nodes\Note $prefix
   * @return nodes\FuncNote
   * @throws Error
   */
  protected function func_note(nodes\Note $prefix): nodes\FuncNote {
    if ($prefix instanceof nodes\GroupedNote) {
      $prefix = $prefix->inner;
    }

    $arrow  = $this->next_punct('->');
    $output = $this->note();
    $span   = Span::join($prefix->get('span'), $output->get('span'));
    return (new nodes\FuncNote($prefix, $output))
      ->set('span', $span);
  }

  /**
   * ( UPPER_NAME "::" )* UPPER_NAME
   *
   * @return nodes\PathNode
   * @throws Error
   */
  protected function upper_path(): nodes\PathNode {
    $super = [];
    while ($this->ahead_is_keyword('super')) {
      $super[] = $this->next_super_name();
      $colons  = $this->next_punct('::');
    }

    $head = [ $this->next_upper_name() ];
    while ($this->ahead_is_punct('::')) {
      $colons = $this->next_punct('::');
      $head[] = $this->next_upper_name();
    }

    $span = Span::join(...array_map(fn(nodes\UpperName $name) => $name->get('span'), $head));
    $tail = array_pop($head);
    return (new PathNode(false, $super, $head, $tail))
      ->set('span', $span);
  }
}
