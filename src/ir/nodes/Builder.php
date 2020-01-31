<?php

namespace Cthulhu\ir\nodes;

use Cthulhu\ir\names\Symbol;
use Cthulhu\ir\types\Type;
use Cthulhu\lib\fmt;
use Cthulhu\val\Value;

class Builder extends fmt\Builder {
  public function increase_indentation(int $n = 2): self {
    return $this->push_frame(function (fmt\Formatter $f) use ($n) {
      $f->increment_tab_stop($n);
    });
  }

  public function decrease_indentation(): self {
    return $this->push_frame(function (fmt\Formatter $f) {
      $f->pop_tab_stop();
    });
  }

  public function indent(): self {
    return $this->push_frame(function (fmt\Formatter $f) {
      $f->tab();
    });
  }

  public function apply_styles(int ...$styles): self {
    return $this->push_frame(function (fmt\Formatter $f) use (&$styles) {
      $f->apply_styles(...$styles);
    });
  }

  public function clear_styles(): self {
    return $this->apply_styles(fmt\Reset::ALL);
  }

  public function paren_left(): self {
    return $this->push_str('(');
  }

  public function paren_right(): self {
    return $this->push_str(')');
  }

  public function keyword(string $keyword): self {
    return $this
      ->apply_styles(fmt\Foreground::BRIGHT_BLACK)
      ->push_str($keyword)
      ->clear_styles();
  }

  public function ident(string $ident): self {
    return $this
      ->apply_styles(fmt\Foreground::BRIGHT_YELLOW)
      ->push_str($ident)
      ->clear_styles();
  }

  public function colon(): self {
    return $this->push_str(':');
  }

  public function space(): self {
    return $this->push_str(' ');
  }

  public function newline(): self {
    return $this->push_str(PHP_EOL);
  }

  public function name(string $text, Symbol $name): self {
    $id = $name->get_id();
    return $this
      ->apply_styles(fmt\Foreground::BRIGHT_BLUE)
      ->push_str($text)
      ->clear_styles()
      ->colon()
      ->apply_styles(fmt\Foreground::YELLOW)
      ->push_str("$$id")
      ->clear_styles();
  }

  public function type(Type $type): self {
    return $this
      ->apply_styles(fmt\Foreground::CYAN)
      ->push_str($type)
      ->clear_styles();
  }

  public function stmts(?Stmt $first): self {
    if ($first) {
      return $this
        ->paren_left()
        ->increase_indentation()
        ->then($first)
        ->decrease_indentation()
        ->paren_right();
    }

    return $this
      ->paren_left()
      ->paren_right();
  }

  /**
   * @param Value $value
   * @return Builder
   */
  public function value(Value $value): self {
    return $this
      ->apply_styles(fmt\Foreground::GREEN)
      ->push_str($value->encode_as_php())
      ->clear_styles();
  }
}
