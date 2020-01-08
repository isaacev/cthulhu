<?php

namespace Cthulhu\lib\fmt;

abstract class Formatter {
  public const MAX_LINE_LENGTH = 80;

  private $col = null;
  private array $tab = [ 0 ];
  private array $applied_styles = [];
  private array $stashed_styles = [];

  abstract protected function write(string $str): void;

  abstract protected function use_color(): bool;

  protected function write_escape_code(int ...$attrs): void {
    if ($this->use_color()) {
      $this->write("\033[" . implode(';', $attrs) . 'm');
    }
  }

  protected function write_text(string $str): void {
    $lines = explode(PHP_EOL, $str);
    if (count($lines) === 1) {
      $this->col += strlen($str);
    } else {
      $this->col = strlen(end($lines));
    }
    $this->write($str);
  }

  protected function current_tab_stop(): int {
    return end($this->tab);
  }

  protected function remaining_space_on_line(): ?int {
    if ($this->col === null) {
      return null;
    }

    return self::MAX_LINE_LENGTH - $this->col;
  }

  /**
   * Add a new tab stop.
   */
  public function push_tab_stop(int $tab_stop): self {
    array_push($this->tab, $tab_stop);
    return $this;
  }

  /**
   * Add a new tab stop that is the current tab stop + `$increment`
   */
  public function increment_tab_stop(int $increment): self {
    return $this->push_tab_stop($this->current_tab_stop() + $increment);
  }

  /**
   * Write spaces until the cursor is at the given tab stop. Does not save the
   * tab stop. If the tab stop is left of the cursor, write nothing.
   */
  public function tab_to(int $tab_stop, string $char = ' '): self {
    $total = max(0, $tab_stop - $this->col);
    return $this->repeat($char, $total);
  }

  /**
   * If the most recent tab stop is right of the current cursor position, write
   * spaces until the cursor is at the tab stop position. If the tab stop is
   * left of the cursor, write nothing.
   */
  public function tab(string $char = ' '): self {
    return $this->tab_to($this->current_tab_stop(), $char);
  }

  /**
   * Remove the most recent tab stop.
   */
  public function pop_tab_stop(): self {
    array_pop($this->tab);
    return $this;
  }

  /**
   * If the current line is shorter than the MAX_LINE_LENGTH, write as many
   * whole `$str`'s as possible without exceeding the MAX_LINE_LENGTH.
   */
  public function fill_line(string $str, int $right_margin = 0): self {
    $available = $this->remaining_space_on_line();
    if ($available === null) {
      return $this;
    }

    while (strlen($str) <= $this->remaining_space_on_line() - $right_margin) {
      $this->write_text($str);
    }

    return $this;
  }

  /**
   * Write 0 or more ANSI escape codes. If the concrete class has disabled
   * colors, this method will write nothing.
   */
  public function apply_styles(int ...$attrs): self {
    $this->write_escape_code(...$attrs);
    $this->applied_styles = array_unique(array_merge($this->applied_styles, $attrs));
    return $this;
  }

  /**
   * Inspired by `git-stash`, the `stash_applied_styles` and `pop_applied_styles`
   * functions allow for temporarily removing the current ANSI styles and then
   * re-applying those styles later.
   *
   * These functions are used by the `text_wrap` method to temporarily remove
   * styles when indenting each line so that background styles are not applied
   * to the indentation whitespace.
   *
   * The `stash_applied_styles` records which styles are currently applied then
   * clears all styling.
   */
  protected function stash_applied_styles(): self {
    array_push($this->stashed_styles, $this->applied_styles);
    return $this->reset_styles();
  }

  /**
   * The `pop_applied_styles` re-applies the stashed styles.
   */
  protected function pop_applied_styles(): self {
    return $this->apply_styles(...array_pop($this->stashed_styles));
  }

  /**
   * Write 0 or more ANSI escape codes if-and-only-if the `$test` parameter is
   * true. If the concrete class has disabled colors, this method will write
   * nothing even if `$test` is true.
   */
  public function apply_styles_if(bool $test, int ...$attrs): self {
    if ($test) {
      return $this->apply_styles(...$attrs);
    } else {
      return $this;
    }
  }

  /**
   * Write the ANSI reset escape code. If the concrete class had disabled
   * colors, this method will write nothing.
   */
  public function reset_styles(): self {
    $this->applied_styles = [];
    $this->write_escape_code(Reset::ALL);
    return $this;
  }

  /**
   * Write the ANSI reset escape code if-and-only-if the `$test` parameter is
   * true. If the concrete class has disabled colors, this method will write
   * nothing even if `$test` is true.
   */
  public function reset_styles_if(bool $test): self {
    if ($test) {
      return $this->reset_styles();
    } else {
      return $this;
    }
  }

  /**
   * Write a string literal.
   */
  public function print(string $str): self {
    $this->write_text($str);
    return $this;
  }

  /**
   * Write a formatted string using the same syntax as PHP's `sprintf`.
   */
  public function printf(string $format, ...$args): self {
    $this->write_text(sprintf($format, ...$args));
    return $this;
  }

  /**
   * Write a string `$num` times. This method does NOT check for line overflow.
   */
  public function repeat(string $str, int $num): self {
    $this->write_text(str_repeat($str, $num));
    return $this;
  }

  /**
   * Write a space `$num` (default 1) times. This method does NOT check for line
   * overflow.
   */
  public function spaces(int $num): self {
    return $this->repeat(' ', $num);
  }

  /**
   * Write a single space character.
   */
  public function space(): self {
    return $this->spaces(1);
  }

  /**
   * Write a newline `$num` (default 1) times.
   */
  public function newlines(int $num): self {
    return $this->repeat(PHP_EOL, $num);
  }

  /**
   * Write a single newline character.
   */
  public function newline(): self {
    return $this->newlines(1);
  }

  public function newline_if_not_already(): self {
    if ($this->col === 0) {
      return $this;
    } else {
      return $this->newline();
    }
  }

  public function text_wrap(string ...$parts): self {
    $whole = implode(' ', $parts);
    $words = preg_split('/\s+/', $whole);
    $count = count($words);
    $index = 0;

    while ($index < $count) {
      $word = $words[$index++];

      // Always write at least 1 word per line, even if the word is longer
      // than the maximum line length.
      $this->print($word);

      while ($index < $count) {
        // Don't increment the `$index` yet because this word could be too long
        // and might not get written on the current line.
        $word = $words[$index];

        if ($this->remaining_space_on_line() < strlen($word) + 1) {
          // There isn't enough space on the current line for both the next
          // word and the space before it so move the word to the next line.
          $this
            ->stash_applied_styles()
            ->newline_if_not_already()
            ->tab()
            ->pop_applied_styles();
          continue 2;

          // IDEA: in the future, instead of immediately pushing a word to the
          // next line when it doesn't fit, consider trying to hyphenate the
          // word and split it across multiple lines:
          // https://nedbatchelder.com/code/modules/hyphenate.html
        }

        // The word fits so increment the `$index` and write the word.
        $index++;
        $this
          ->space()
          ->print($word);
      }
    }
    return $this;
  }
}
