<?php

namespace Cthulhu\php;

use Cthulhu\lib\fmt;
use Cthulhu\php\nodes\Precedence;
use Cthulhu\php\nodes\Reference;
use Cthulhu\val\Value;

class Builder implements Buildable {
  private array $frames = [];

  private function push_frame(callable $frame): self {
    array_push($this->frames, $frame);
    return $this;
  }

  private function push_str(string $str): self {
    return $this->push_frame(function (fmt\Formatter $f) use ($str) {
      $f->print($str);
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
   * Apply accumulated frames to a fmt\Formatter
   *
   * @param fmt\Formatter $f
   * @return fmt\Formatter
   */
  public function write(fmt\Formatter $f): fmt\Formatter {
    foreach ($this->frames as $frame) {
      call_user_func($frame, $f);
    }
    return $f;
  }

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

  public function bracket_left(): self {
    return $this->push_str('[');
  }

  public function bracket_right(): self {
    return $this->push_str(']');
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

  public function dot(): self {
    return $this->push_str('.');
  }

  public function fat_arrow(): self {
    return $this->push_str('=>');
  }

  public function thin_arrow(): self {
    return $this->push_str('->');
  }

  public function single_quote(): self {
    return $this->push_str('\'');
  }

  public function variable(string $name): self {
    return $this->push_str('$' . $name);
  }

  public function identifier(string $ident): self {
    return $this->push_str($ident);
  }

  public function reference(Reference $ref): self {
    return $this
      ->backslash()
      ->then($ref);
  }

  public function keyword(string $keyword): self {
    return $this->push_str($keyword);
  }

  public function operator(string $operator): self {
    return $this->push_str($operator);
  }

  public function value(Value $value): self {
    return $this->push_str($value->encode_as_php());
  }

  public function null_literal(): self {
    return $this->push_str('null');
  }

  public function comment(string $message): self {
    // FIXME: comment message could multiple lines
    return $this->push_str("// $message");
  }

  public function block_comment(string $message): self {
    return $this->push_str('/* ' . $message . ' */');
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
    return $this->push_frame(function (fmt\Formatter $f) {
      $f->tab();
    });
  }

  public function newline_then_indent(): self {
    return $this
      ->newline()
      ->indent();
  }

  public function increase_indentation(): self {
    return $this->push_frame(function (fmt\Formatter $f) {
      $f->increment_tab_stop(2);
    });
  }

  public function decrease_indentation(): self {
    return $this->push_frame(function (fmt\Formatter $f) {
      $f->pop_tab_stop();
    });
  }

  public function expr(nodes\Expr $expr, int $parent_precedence = Precedence::LOWEST): self {
    $should_group = $expr->precedence() < $parent_precedence;
    return $this
      ->maybe($should_group, (new Builder)->paren_left())
      ->then($expr)
      ->maybe($should_group, (new Builder)->paren_right());
  }

  public function block(nodes\BlockNode $block, ?Builder $spacer = null): self {
    if ($spacer) {
      $spacer = (new Builder)
        ->newline()
        ->then($spacer)
        ->indent();
    } else {
      $spacer = (new Builder)
        ->newline_then_indent();
    }

    $is_empty = (new Builder)
      ->comment('empty');

    $not_empty = (new Builder)
      ->each($block->stmts, $spacer);

    return $this
      ->brace_left()
      ->increase_indentation()
      ->newline_then_indent()
      ->choose($block->is_empty(), $is_empty, $not_empty)
      ->decrease_indentation()
      ->newline_then_indent()
      ->brace_right();
  }

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
