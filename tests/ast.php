<?php

use \Cthulhu\Parser\AST;

function str($value) {
  return new AST\StrLiteralExpression($value, '"' . $value . '"');
}

function num($value) {
  return new AST\NumLiteralExpression($value, "$value");
}

function ident($name) {
  return new AST\Identifier($name);
}

function binary($op, $left, $right) {
  return new AST\BinaryOperator($op, $left, $right);
}

function block($stmts) {
  return new AST\Block($stmts);
}

function ifelse($cond, $if_clause, $else_clause) {
  return new AST\IfExpression($cond, $if_clause, $else_clause);
}

function exprStmt($expr) {
  return new AST\ExpressionStatement($expr);
}

function nameNote($name) {
  return new AST\NamedAnnotation($name);
}

function param($name, $note) {
  return [
    'name' => $name,
    'annotation' => $note
  ];
}

function fn($params, $ret, $body) {
  return new AST\FnExpression($params, $ret, $body);
}

function call($callee, $args) {
  return new AST\CallExpression($callee, $args);
}

function let($name, $expr) {
  return new AST\LetStatement($name, $expr);
}

function root($stmts) {
  return new AST\Root($stmts);
}
