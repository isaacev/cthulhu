<?php

namespace Cthulhu\Parser\Lexer;

abstract class TokenType {
  const IDENT         = 'Ident';
  const KEYWORD_LET   = 'Let';
  const KEYWORD_IF    = 'If';
  const KEYWORD_ELSE  = 'Else';
  const KEYWORD_FN    = 'Fn';
  const BRACE_LEFT    = '{';
  const BRACE_RIGHT   = '}';
  const BRACKET_LEFT  = '[';
  const BRACKET_RIGHT = ']';
  const PAREN_LEFT    = '(';
  const PAREN_RIGHT   = ')';
  const PLUS          = '+';
  const DASH          = '-';
  const STAR          = '*';
  const SLASH         = '/';
  const SEMICOLON     = ';';
  const EQUALS        = '=';
  const COLON         = ':';
  const COMMA         = ',';
}
