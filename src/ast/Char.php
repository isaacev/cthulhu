<?php

namespace Cthulhu\ast;

use Cthulhu\loc\Point;

class Char {
  public Point $point;
  public string $raw_char;

  public function __construct(Point $point, string $raw_char) {
    $this->point    = $point;
    $this->raw_char = $raw_char;
  }

  public static function is(string $raw_char, self $char): bool {
    return $char->raw_char === $raw_char;
  }

  /**
   * @param string[] $raw_chars
   * @param self     $char
   * @return bool
   */
  public static function is_one_of(array $raw_chars, self $char): bool {
    return in_array($char->raw_char, $raw_chars);
  }

  /**
   * @param string[] $raw_chars
   * @param Char     $char
   * @return bool
   */
  public static function is_not_one_of(array $raw_chars, self $char): bool {
    return !self::is_one_of($raw_chars, $char);
  }

  public static function is_eof(self $char): bool {
    return self::is('', $char);
  }

  public static function is_newline(self $char): bool {
    return self::is("\n", $char);
  }

  public static function is_underscore(self $char): bool {
    return self::is('_', $char);
  }

  public static function is_double_quote(self $char): bool {
    return self::is('"', $char);
  }

  public static function is_dot(self $char): bool {
    return self::is('.', $char);
  }

  public static function is_whitespace(self $char): bool {
    return self::is_one_of([ ' ', "\n", "\t" ], $char);
  }

  /**
   * @param Char $char
   * @return bool
   */
  public static function is_delim(self $char): bool {
    return self::is_one_of([ '(', ')', '[', ']', '{', '}' ], $char);
  }

  public static function is_between(string $lo, string $hi, self $char): bool {
    return (
      !self::is_eof($char) &&
      $lo <= $char->raw_char &&
      $char->raw_char <= $hi
    );
  }

  public static function is_upper_letter(self $char): bool {
    return self::is_between('A', 'Z', $char);
  }

  public static function is_lower_letter(self $char): bool {
    return self::is_between('a', 'z', $char);
  }

  public static function is_letter(self $char): bool {
    return self::is_upper_letter($char) || self::is_lower_letter($char);
  }

  public static function is_digit(self $char): bool {
    return self::is_between('0', '9', $char);
  }

  public static function is_alphanumeric(self $char): bool {
    return self::is_letter($char) || self::is_digit($char) || self::is_underscore($char);
  }
}
