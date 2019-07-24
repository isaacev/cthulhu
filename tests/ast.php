<?php

use \Cthulhu\Parser\AST;
use \Cthulhu\Parser\Lexer\Point;
use \Cthulhu\Parser\Lexer\Span;

function str($value) {
  $span = new Span(new Point(), new Point());
  return new AST\StrLiteralExpression($span, $value, '"' . $value . '"');
}

function num($value) {
  $span = new Span(new Point(), new Point());
  return new AST\NumLiteralExpression($span, $value, "$value");
}

function ident($name) {
  $span = new Span(new Point(), new Point());
  return new AST\Identifier($span, $name);
}

function binary($op, $left, $right) {
  $span = new Span(new Point(), new Point());
  return new AST\BinaryOperator($span, $op, $left, $right);
}

function block($stmts) {
  return new AST\Block($stmts);
}

function ifelse($cond, $if_clause, $else_clause) {
  $span = new Span(new Point(), new Point());
  return new AST\IfExpression($span, $cond, $if_clause, $else_clause);
}

function exprStmt($expr) {
  $span = new Span(new Point(), new Point());
  return new AST\ExpressionStatement($span, $expr);
}

function nameNote($name) {
  $span = new Span(new Point(), new Point());
  return new AST\NamedAnnotation($span, $name);
}

function param($name, $note) {
  return [
    'name' => $name,
    'annotation' => $note
  ];
}

function fn($params, $ret, $body) {
  $span = new Span(new Point(), new Point());
  return new AST\FnExpression($span, $params, $ret, $body);
}

function call($callee, $args) {
  $span = new Span(new Point(), new Point());
  return new AST\CallExpression($span, $callee, $args);
}

function let($name, $expr) {
  $span = new Span(new Point(), new Point());
  return new AST\LetStatement($span, $name, $expr);
}

function root($stmts) {
  return new AST\Root($stmts);
}
