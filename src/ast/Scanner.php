<?php

namespace Cthulhu\ast;

use Cthulhu\loc;

class Scanner {
  private loc\File $file;
  private array $raw_chars;
  private int $offset = 0;
  private loc\Point $point;
  private ?Char $buffer = null;

  public function __construct(loc\File $file) {
    $this->file      = $file;
    $this->raw_chars = self::string_to_raw_chars($file->contents);
    $this->point     = new loc\Point($file, 1, 1);
  }

  /**
   * Return the next `Char` without advancing the scanner. Repeated calls will
   * return the same `Char`.
   *
   * @return Char
   */
  public function peek(): Char {
    if ($this->buffer === null) {
      $this->buffer = $this->next();
    }

    return $this->buffer;
  }

  /**
   * Return the next `Char`then advance the scanner. Repeated calls will return
   * subsequent `Char`s.
   *
   * @return Char|null
   */
  public function next(): ?Char {
    if ($this->buffer !== null) {
      $old_buffer   = $this->buffer;
      $this->buffer = null;
      return $old_buffer;
    }

    if ($this->is_done()) {
      return new Char($this->point, '');
    }

    $raw_char    = $this->raw_chars[$this->offset++];
    $old_point   = $this->point;
    $this->point = $raw_char === PHP_EOL
      ? $this->point->next_line()
      : $this->point->next_column();
    return new Char($old_point, $raw_char);
  }

  /**
   * Given a `$predicate` with the signature `Char -> bool`, skip any subsequent
   * `Char` for which `$predicate` returns true.
   *
   * @param callable $predicate
   */
  public function skip_while(callable $predicate): void {
    while ($predicate($this->peek())) {
      $this->next();
    }
  }

  private function is_done(): bool {
    return $this->offset >= count($this->raw_chars);
  }

  /**
   * @param string $str
   * @return string[]
   */
  private static function string_to_raw_chars(string $str): array {
    return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
  }
}
