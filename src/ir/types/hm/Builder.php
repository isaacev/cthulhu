<?php

namespace Cthulhu\ir\types\hm;

use Cthulhu\ast\nodes\Pattern;
use Cthulhu\ir\names\Symbol;
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
    return $this->push_str($keyword);
  }

  public function lambda(): self {
    return $this->push_str('λ');
  }

  public function pattern(Pattern $pattern): self {
    return $this
      ->apply_styles(fmt\Foreground::MAGENTA)
      ->push_str("$pattern")
      ->clear_styles();
  }

  public function let(): self {
    return $this->push_str('let');
  }

  public function space(): self {
    return $this->push_str(' ');
  }

  public function newline(): self {
    return $this->push_str(PHP_EOL);
  }

  public function name(Symbol $name): self {
    $id = $name->get_id();
    return $this
      ->apply_styles(fmt\Foreground::YELLOW)
      ->push_str("$$id")
      ->clear_styles();
  }

  public function type(Type $type): self {
    return $this
      ->apply_styles(fmt\Foreground::CYAN)
      ->push_str("$type")
      ->clear_styles();
  }

  /**
   * @param Value $value
   * @return $this
   */
  public function value(Value $value): self {
    return $this
      ->apply_styles(fmt\Foreground::GREEN)
      ->push_str($value->encode_as_php())
      ->clear_styles();
  }
}
