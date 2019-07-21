<?php

function ident($name) {
  return [
    'type' => 'Identifier',
    'name' => $name
  ];
}

function binary($op, $left, $right) {
  return [
    'type' => 'BinaryOperator',
    'operator' => $op,
    'left' => $left,
    'right' => $right
  ];
}

function ifelse($cond, $if_clause, $else_clause) {
  return [
    'type' => 'IfExpression',
    'condition' => $cond,
    'if_clause' => $if_clause,
    'else_clause' => $else_clause
  ];
}

function exprStmt($expr) {
  return [
    'type' => 'ExpressionStatement',
    'expression' => $expr
  ];
}

function nameNote($name) {
  return [
    'type' => 'NamedAnnotation',
    'name' => $name
  ];
}

function param($name, $note) {
  return [
    'name' => $name,
    'annotation' => $note
  ];
}

function fn($params, $ret, $body) {
  return [
    'type' => 'FnExpression',
    'parameters' => $params,
    'return_annotation' => $ret,
    'body' => $body
  ];
}

function call($callee, $args) {
  return [
    'type' => 'CallExpression',
    'callee' => $callee,
    'arguments' => $args
  ];
}

function let($name, $expr) {
  return [
    'type' => 'LetStatement',
    'name' => $name,
    'expression' => $expr
  ];
}

function root($stmts) {
  return [
    'type' => 'Root',
    'statements' => $stmts
  ];
}
