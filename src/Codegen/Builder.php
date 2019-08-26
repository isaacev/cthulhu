<?php

namespace Cthulhu\Codegen;

class Builder implements Buildable {
  private $frames = [];

  /**
   * Internal methods
   */
  private function push_frame(callable $frame): self {
    array_push($this->frames, $frame);
    return $this;
  }

  private function push_str(string $str): self {
    return $this->push_frame(function (Writer $w) use ($str) {
      $w->write($str);
    });
  }

  private function push_builder(Builder $builder): self {
    $this->frames = array_merge($this->frames, $builder->frames);
    return $this;
  }

  /**
   * Interface implementation methods
   */
  public function build(): Builder {
    return $this;
  }

  /**
   * Apply accumulated frames to a Writer
   */
  public function write(Writer $writer): Writer {
    foreach ($this->frames as $frame) {
      call_user_func($frame, $writer);
    }
    return $writer;
  }

  /**
   * Operators and other symbols
   */
  public function opening_php_tag(): self {
    return $this
      ->push_str('<?php')
      ->newline();
  }

  public function paren_left(): self {
    return $this->push_str('(');
  }

  public function paren_right(): self {
    return $this->push_str(')');
  }

  public function brace_left(): self {
    return $this->push_str('{');
  }

  public function brace_right(): self {
    return $this->push_str('}');
  }

  public function comma(): self {
    return $this->push_str(',');
  }

  public function semicolon(): self {
    return $this->push_str(';');
  }

  public function equals(): self {
    return $this->push_str('=');
  }

  public function backslash(): self {
    return $this->push_str('\\');
  }

  /**
   * Variables, keywords, and other literals
   */
  public function variable(string $name): self {
    return $this->push_str('$' . $name);
  }

  public function identifier(string $ident): self {
    return $this->push_str($ident);
  }

  public function reference(array $segments): self {
    return $this->push_str(implode('\\', $segments));
  }

  public function keyword(string $keyword): self {
    return $this->push_str($keyword);
  }

  public function operator(string $operator): self {
    return $this->push_str($operator);
  }

  public function string_literal(string $value): self {
    // TODO: will string escaping be handled here or by the parser?
    return $this->push_str('"' . $value . '"');
  }

  public function int_literal(int $value): self {
    return $this->push_str("$value");
  }

  public function comment(string $message): self {
    // FIXME: comment message could multiple lines
    return $this->push_str("// $message");
  }

  /**
   * Whitespace and indentation
   */
  public function space(): self {
    return $this->push_str(' ');
  }

  public function newline(): self {
    return $this->push_str("\n");
  }

  public function indent(): self {
    return $this->push_frame(function (Writer $w) {
      $w->write($w->get_indentation());
    });
  }

  public function newline_then_indent(): self {
    return $this
      ->newline()
      ->indent();
  }

  public function increase_indentation(): self {
    return $this->push_frame(function (Writer $w) {
      $w->increase_indentation();
    });
  }

  public function decrease_indentation(): self {
    return $this->push_frame(function (Writer $w) {
      $w->decrease_indentation();
    });
  }

  /**
   * Statements, expressions, and other syntax nodes
   */
  public function expr(PHP\Expr $expr, int $parent_precedence = 0): self {
    $should_group = $expr->precedence() < $parent_precedence;
    return $this
      ->maybe($should_group, (new Builder)->paren_left())
      ->then($expr)
      ->maybe($should_group, (new Builder)->paren_right());
  }

  public function stmts(array $stmts): self {
    return $this
      ->each($stmts, (new Builder)->newline_then_indent());
  }

  /**
   * Builder composition
   */
  public function then(Buildable $buildable): self {
    return $this->push_builder($buildable->build());
  }

  public function maybe(bool $test, Buildable $if_true): self {
    if ($test) {
      return $this->then($if_true);
    } else {
      return $this;
    }
  }

  public function choose(bool $test, Buildable $if_true, Buildable $if_false): self {
    if ($test) {
      return $this->then($if_true);
    } else {
      return $this->then($if_false);
    }
  }

  public function each(array $buildables, ?Buildable $glue = null): self {
    foreach ($buildables as $i => $buildable) {
      if ($glue !== null && $i > 0) {
        $this->then($glue);
      }
      $this->then($buildable);
    }
    return $this;
  }
}
