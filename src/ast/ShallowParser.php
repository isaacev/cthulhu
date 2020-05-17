<?php

/* @noinspection PhpUnusedLocalVariableInspection */

namespace Cthulhu\ast;

use Cthulhu\ast\tokens\IdentToken;
use Cthulhu\err\Error;
use Cthulhu\loc\File;
use Cthulhu\loc\Point;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

class ShallowParser extends AbstractParser {
  private File $file;

  /**
   * @param File   $file
   * @param Nester $nester
   * @throws Error
   */
  public function __construct(File $file, Nester $nester) {
    $this->file = $file;
    $this->begin_parsing($nester->nest());
  }

  /**
   * @return nodes\ShallowFile
   * @throws Error
   */
  public function file(): nodes\ShallowFile {
    $items = $this->items();
    $span  = empty($items)
      ? (new Point($this->file, 1, 1))->span()
      : Span::join(...array_map(fn(nodes\ShallowItem $i) => $i->get('span'), $items));
    return (new nodes\ShallowFile($this->file, $items))
      ->set('span', $span);
  }

  /**
   * @param Spanlike          $spanlike
   * @param nodes\Attribute[] $attrs
   * @return array
   * @throws Error
   */
  private function find_precedence_attr(Spanlike $spanlike, array $attrs): array {
    foreach ($attrs as $attr) {
      if ($attr->name->value === 'infix' || $attr->name->value === 'infixr') {
        if (count($attr->args) !== 1) {
          throw Errors::wrong_attr_arity($attr, 1);
        }
        $precedence_name = $attr->args[0]->value;
        $is_right_assoc  = $attr->name->value === 'infixr';
        $minimum_arity   = 2;
        switch ($precedence_name) {
          case 'rel':
            return [ Precedence::RELATION, $is_right_assoc, $minimum_arity ];
          case 'sum':
            return [ Precedence::SUM, $is_right_assoc, $minimum_arity ];
          case 'prod':
            return [ Precedence::PRODUCT, $is_right_assoc, $minimum_arity ];
          case 'pipe':
            return [ Precedence::PIPE, $is_right_assoc, $minimum_arity ];
          case 'exp':
            return [ Precedence::EXPONENT, $is_right_assoc, $minimum_arity ];
          default:
            throw Errors::unknown_attr_arg($attr->args[0]);
        }
      } else if ($attr->name->value === 'prefix') {
        $precedence_name = 'prefix';
        $is_right_assoc  = false;
        $minimum_arity   = 1;
        return [ Precedence::UNARY, $is_right_assoc, $minimum_arity ];
      }
    }
    throw Errors::missing_precedence_attr($spanlike);
  }

  /**
   * @return nodes\Attribute[]
   * @throws Error
   */
  private function attributes(): array {
    $attrs = [];
    while ($this->ahead_is_punct('#')) {
      $attrs[] = $this->attribute();
    }
    return $attrs;
  }

  /**
   * @return nodes\Attribute
   * @throws Error
   */
  private function attribute(): nodes\Attribute {
    $pound      = $this->next_punct_span('#');
    $brackets   = $this->next_group_matches('[]');
    $name       = $this->next_lower_name();
    $maybe_args = $this->peek_group();
    if ($maybe_args === null) {
      $args = [];
    } else if ($maybe_args->delim === '()') {
      $this->next_group_matches('()');
      $args = $this->one_or_more_comma_separated_names();
      $this->exit_group_matches('()');
    } else {
      throw Errors::expected_token($maybe_args->left, 'attribute arguments');
    }

    $this->exit_group_matches('[]');
    $span = Span::join($pound, $brackets->right);
    return (new nodes\Attribute($name, $args))
      ->set('span', $span);
  }

  /**
   * @return nodes\ShallowItem[]
   * @throws Error
   */
  private function items(): array {
    $items = [];
    while (true) {
      $attrs     = $this->attributes();
      $maybe_pub = $this->ahead_is_keyword('pub')
        ? $this->next_keyword('pub')
        : null;

      $peek = $this->peek_token();
      if ($peek instanceof IdentToken) {
        switch ($peek->lexeme) {
          case 'type':
            $item = $this->type_item($maybe_pub);
            break;
          case 'intrinsic':
            $item = $this->intrinsic_item($maybe_pub);
            break;
          case 'mod':
            $item = $this->mod_item($maybe_pub);
            break;
          case 'use':
            $item = $this->use_item($maybe_pub);
            break;
          case 'fn':
            $item = $this->fn_item($maybe_pub, $attrs);
            break;
          default:
            throw Errors::expected_item($peek);
        }

        $item->set('attrs', $attrs);
        $items[] = $item;
      } else if ($peek === null) {
        break;
      } else {
        throw Errors::expected_item($peek);
      }
    }
    return $items;
  }

  /**
   * @param IdentToken|null $maybe_pub
   * @return nodes\ShallowEnumItem
   * @throws Error
   */
  private function type_item(?IdentToken $maybe_pub): nodes\ShallowEnumItem {
    $keyword = $this->next_keyword('type');
    $name    = $this->next_upper_name();
    if ($this->ahead_is_group('()')) {
      $enter_group = $this->next_group_matches('()');
      $params      = $this->one_or_more_param_notes();
      $exit_group  = $this->exit_group_matches('()');
    } else {
      $params = [];
    }
    $equals = $this->next_punct('=');
    $forms  = $this->one_or_more_form_decls();
    $semi   = $this->next_punct_span(';');
    $span   = Span::join($maybe_pub ?? $keyword, $semi);
    return (new nodes\ShallowEnumItem($name, $params, $forms))
      ->set('pub', $maybe_pub !== null)
      ->set('span', $span);
  }

  /**
   * @return nodes\TypeParamNote[]
   * @throws Error
   */
  private function one_or_more_param_notes(): array {
    $params = [ $this->param_note() ];
    while ($this->ahead_is_punct(',')) {
      $comma    = $this->next_punct(',');
      $params[] = $this->param_note();
    }
    return $params;
  }

  /**
   * @return nodes\FormDecl[]
   * @throws Error
   */
  private function one_or_more_form_decls(): array {
    $variants = [ $this->form_decl() ];
    while ($this->ahead_is_punct('|')) {
      $variants[] = $this->form_decl();
    }
    return $variants;
  }

  /**
   * @return nodes\FormDecl
   * @throws Error
   */
  private function form_decl(): nodes\FormDecl {
    $pipe = $this->next_punct_span('|');
    $name = $this->next_upper_name();

    if ($this->ahead_is_group('{}')) {
      $enter_group = $this->next_group_matches('{}');
      $params      = $this->one_or_more_name_type_pairs();
      $exit_group  = $this->exit_group_matches('{}');
      $span        = Span::join($pipe, $exit_group);
      return (new nodes\NamedFormDecl($name, $params))
        ->set('span', $span);
    }

    if ($this->ahead_is_group('()')) {
      $enter_group = $this->next_group_matches('()');
      $params      = $this->one_or_more_notes();
      $exit_group  = $this->exit_group_matches('()');
      $span        = Span::join($pipe, $exit_group);
      return (new nodes\OrderedFormDecl($name, $params))
        ->set('span', $span);
    }

    $span = Span::join($pipe, $name->get('span'));
    return (new nodes\NullaryFormDecl($name))
      ->set('span', $span);
  }

  /**
   * @param IdentToken|null $maybe_pub
   * @return nodes\ShallowIntrinsicItem
   * @throws Error
   */
  private function intrinsic_item(?IdentToken $maybe_pub): nodes\ShallowIntrinsicItem {
    $keyword     = $this->next_keyword('intrinsic');
    $enter_block = $this->next_group_matches('{}');
    $signatures  = $this->intrinsic_signatures();
    $exit_block  = $this->exit_group_matches('{}');
    $span        = Span::join($maybe_pub ?? $keyword, $exit_block);
    return (new nodes\ShallowIntrinsicItem($signatures))
      ->set('pub', $maybe_pub !== null)
      ->set('span', $span);
  }

  /**
   * @return nodes\IntrinsicSignature[]
   * @throws Error
   */
  private function intrinsic_signatures(): array {
    $signatures = [];
    while ($this->peek_token()) {
      $signatures[] = $this->intrinsic_signature();
      $semi         = $this->next_punct(';');
    }
    return $signatures;
  }

  /**
   * @return nodes\IntrinsicSignature
   * @throws Error
   */
  private function intrinsic_signature(): nodes\IntrinsicSignature {
    $keyword = $this->next_keyword('fn');
    $name    = $this->next_lower_name();
    $params  = $this->grouped_note();
    $arrow   = $this->next_punct('->');
    $returns = $this->note();
    $span    = Span::join($keyword, $returns->get('span'));
    return (new nodes\IntrinsicSignature($name, $params, $returns))
      ->set('span', $span);
  }

  /**
   * @param IdentToken|null $maybe_pub
   * @return nodes\ShallowModItem
   * @throws Error
   */
  private function mod_item(?IdentToken $maybe_pub): nodes\ShallowModItem {
    $keyword    = $this->next_keyword('mod');
    $name       = $this->next_upper_name();
    $enter_body = $this->next_group_matches('{}');
    $body       = $this->items();
    $exit_body  = $this->exit_group_matches('{}');
    $span       = Span::join($maybe_pub ?? $keyword, $exit_body);
    return (new nodes\ShallowModItem($name, $body))
      ->set('pub', $maybe_pub !== null)
      ->set('span', $span);
  }

  /**
   * @param IdentToken|null $maybe_pub
   * @return nodes\ShallowUseItem
   * @throws Error
   */
  private function use_item(?IdentToken $maybe_pub): nodes\ShallowUseItem {
    $keyword = $this->next_keyword('use');
    $path    = $this->use_path();
    $semi    = $this->next_punct_span(';');
    $span    = Span::join($maybe_pub ?? $keyword, $semi);
    return (new nodes\ShallowUseItem($path))
      ->set('pub', $maybe_pub !== null)
      ->set('span', $span);
  }

  /**
   * @param nodes\Attribute[] $attrs
   * @return nodes\FnName
   * @throws Error
   */
  private function fn_name(array $attrs): nodes\FnName {
    if ($this->ahead_is_lower_ident()) {
      return $this->next_lower_name();
    } else if ($this->ahead_is_upper_ident()) {
      throw Errors::expected_token($this->peek_token(), 'lowercase identifier');
    } else if ($this->ahead_is_reserved()) {
      throw Errors::used_reserved_ident($this->peek_token());
    } else {
      $enter_parens = $this->next_group_matches('()');

      $tokens = $this->next_contiguous_punct();
      $span   = Span::join(...$tokens);
      $value  = $this->tokens_to_string($tokens);
      [ $prec, $is_right_assoc, $min_arity ] = $this->find_precedence_attr($span, $attrs);
      $oper        = (new nodes\Operator($prec, $is_right_assoc, $value, $min_arity))->set('span', $span);
      $exit_parens = $this->exit_group_matches('()');
      return $oper;
    }
  }

  /**
   * @return nodes\ParamNode[]
   * @throws Error
   */
  private function zero_or_more_name_type_pairs(): array {
    if ($this->peek_token() === null) {
      return [];
    }
    return $this->one_or_more_name_type_pairs();
  }

  /**
   * @return nodes\ParamNode[]
   * @throws Error
   */
  private function one_or_more_name_type_pairs(): array {
    $params = [ $this->name_type_pair() ];
    while ($this->ahead_is_punct(',')) {
      $comma    = $this->next_punct(',');
      $params[] = $this->name_type_pair();
    }
    return $params;
  }

  /**
   * @return nodes\ParamNode
   * @throws Error
   */
  private function name_type_pair(): nodes\ParamNode {
    $name  = $this->next_lower_name();
    $colon = $this->next_punct(':');
    $note  = $this->note();
    $span  = Span::join($name->get('span'), $note->get('span'));
    return (new nodes\ParamNode($name, $note))
      ->set('span', $span);
  }

  /**
   * @param IdentToken|null   $maybe_pub
   * @param nodes\Attribute[] $attrs
   * @return nodes\ShallowFnItem
   * @throws Error
   */
  private function fn_item(?IdentToken $maybe_pub, array $attrs): nodes\ShallowFnItem {
    $keyword      = $this->next_keyword('fn');
    $name         = $this->fn_name($attrs);
    $enter_params = $this->next_group_matches('()');
    $params       = $this->zero_or_more_name_type_pairs();
    $exit_params  = $this->exit_group_matches('()');
    $params       = (new nodes\FnParams($params))->set('span', Span::join($enter_params, $exit_params));

    if ($name instanceof nodes\Operator && count($params) < $name->min_arity) {
      throw Errors::wrong_prec_arity($params->get('span'), $name->min_arity, count($params));
    }

    $arrow   = $this->next_punct('->');
    $returns = $this->note();
    $body    = $this->shallow_block();

    $span = Span::join($maybe_pub ?? $keyword, $body->get('span'));
    return (new nodes\ShallowFnItem($name, $params, $returns, $body))
      ->set('pub', $maybe_pub !== null)
      ->set('span', $span);
  }

  /**
   * ( "::" | ( (SUPER_NAME "::" )+ ) )? ( UPPER_NAME "::" )* UPPER_NAME ( "::" ( LOWER_NAME | "*" ) )?
   *
   * @return nodes\CompoundPathNode
   * @throws Error
   */
  private function use_path(): nodes\CompoundPathNode {
    $is_extern = false;
    $super     = [];
    $head      = [];
    $span      = null;
    $is_done   = false;

    if ($this->ahead_is_punct('::')) {
      $is_extern = true;
      $span      = $this->next_punct_span('::');
    } else if ($this->ahead_is_keyword('super')) {
      do {
        $super[] = $this->next_super_name();
        $span    = $span ?? end($super)->get('span');
        if ($this->ahead_is_punct('::')) {
          $colons = $this->next_punct('::');
        } else {
          break;
        }
      } while ($this->ahead_is_keyword('super'));
    }

    while ($this->ahead_is_upper_ident()) {
      $head[] = $this->next_upper_name();
      $span   = $span ?? end($head)->get('span');
      if ($this->ahead_is_punct('::')) {
        $colons = $this->next_punct('::');
      } else {
        $is_done = true;
        break;
      }
    }

    if (empty($head)) {
      $head[] = $this->next_upper_name();
      $span   = end($head)->get('span');
    }

    if ($is_done || $this->ahead_is_punct(';')) {
      $tail = array_pop($head);
      return (new nodes\CompoundPathNode($is_extern, $super, $head, $tail))
        ->set('span', $span);
    } else {
      if ($this->ahead_is_lower_ident()) {
        $tail = $this->next_lower_name();
        return (new nodes\CompoundPathNode($is_extern, $super, $head, $tail))
          ->set('span', Span::join($span, $tail->get('span')));
      } else {
        $tail = (new nodes\StarSegment())
          ->set('span', $this->next_punct_span('*'));
        return (new nodes\CompoundPathNode($is_extern, $super, $head, $tail))
          ->set('span', Span::join($span, $tail->get('span')));
      }
    }
  }
}
