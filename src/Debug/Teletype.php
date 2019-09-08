<?php

namespace Cthulhu\Debug;

abstract class Teletype {
  const MAX_LINE_WIDTH = 80;

  private $col = null;
  private $tab = [0];

  function __construct(array $options = []) {
    $this->options = $options;
    $this->color = $this->get_option('color', true);
  }

  abstract protected function write(string $str): void;

  protected function get_option(string $name, $fallback) {
    if (array_key_exists($name, $this->options)) {
      return $this->options[$name];
    } else {
      return $fallback;
    }
  }

  protected function append_escape_code(int ...$attrs): void {
    if ($this->color) {
      $this->write("\033[" . implode(';', $attrs) . 'm');
    }
  }

  protected function append_text(string $str): void {
    $lines = explode(PHP_EOL, $str);
    if (count($lines) === 1) {
      $this->col += strlen($str);
    } else {
      $this->col = strlen(end($lines));
    }
    $this->write($str);
  }

  protected function get_tab_stop(): int {
    return end($this->tab);
  }

  public function increase_tab_stop(int $increment): self {
    $this->tab[] = $this->get_tab_stop() + $increment;
    return $this;
  }

  public function tab(): self {
    return $this
      ->newline_if_not_empty()
      ->spaces($this->get_tab_stop());
  }

  public function pop_tab_stop(): self {
    array_pop($this->tab);
    return $this;
  }

  public function space_left_on_line(): ?int {
    if ($this->col === null) {
      return null;
    }

    return self::MAX_LINE_WIDTH - $this->col;
  }

  public function fill_line(string $char, int $cutoff = 0): self {
    $available = $this->space_left_on_line();
    if ($available === null) {
      return $this;
    }

    while (strlen($char) <= $this->space_left_on_line() - $cutoff) {
      $this->printf($char);
    }

    return $this;
  }

  public function apply_styles(int ...$attrs): self {
    $this->append_escape_code(...$attrs);
    return $this;
  }

  public function apply_styles_if(bool $test, int ...$attrs): self {
    if ($test) {
      return $this->apply_styles(...$attrs);
    } else {
      return $this;
    }
  }

  public function reset_styles(): self {
    $this->append_escape_code(Reset::ALL);
    return $this;
  }

  public function reset_styles_if(bool $test): self {
    if ($test) {
      return $this->reset_styles();
    } else {
      return $this;
    }
  }

  public function printf(string $format, ...$args): self {
    $this->append_text(sprintf($format, ...$args));
    return $this;
  }

  public function repeat(string $str, int $num): self {
    $this->append_text(str_repeat($str, $num));
    return $this;
  }

  public function spaces(int $num) {
    return $this->repeat(' ', $num);
  }

  public function newline(): self {
    $this->append_text(PHP_EOL);
    return $this;
  }

  public function newline_if_not_empty(): self {
    if ($this->col === 0) {
      // Already on an empty newline
      return $this;
    } else {
      return $this->newline();
    }
  }
}
