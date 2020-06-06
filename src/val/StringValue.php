<?php

namespace Cthulhu\val;

const ESCAPE_KEYS = [
  'n' => "\n",
  't' => "\t",
  'r' => "\r",
  '"' => '"',
  '\\' => '\\',
];

class StringValue extends Value {
  public string $raw;
  public string $value;
  public string $escaped;

  public function __construct(string $raw, string $value, string $escaped) {
    $this->raw     = $raw;
    $this->value   = $value;
    $this->escaped = $escaped;
  }

  public function append(StringValue $other): StringValue {
    $raw     = substr($this->raw, 0, -1) . substr($other->raw, 1);
    $value   = $this->value . $other->value;
    $escaped = substr($this->escaped, 0, -1) . substr($other->escaped, 1);
    return new StringValue($raw, $value, $escaped);
  }

  public function encode_as_php(): string {
    return $this->escaped;
  }

  public static function from_safe_scalar(string $raw): StringValue {
    return new StringValue($raw, $raw, '"' . $raw . '"');
  }

  /**
   * @param string $raw
   * @return StringValue
   * @throws UnknownEscapeChar
   */
  public static function from_scalar(string $raw): StringValue {
    $value   = '';
    $escaped = '';

    $is_esc = false;
    for ($i = 1, $len = strlen($raw); $i < $len - 1; $i++) {
      $char = $raw[$i];
      if ($is_esc) {
        if (array_key_exists($char, ESCAPE_KEYS)) {
          $value   .= ESCAPE_KEYS[$char];
          $escaped .= '\\' . $char;
          $is_esc  = false;
        } else {
          throw new UnknownEscapeChar($i);
        }
      } else if ($char === '\\') {
        $is_esc = true;
      } else if ($char === '$') {
        $value   .= '$';
        $escaped .= '\\$';
      } else {
        $value   .= $char;
        $escaped .= $char;
      }
    }

    $escaped = '"' . $escaped . '"';
    return new self($raw, $value, $escaped);
  }
}
