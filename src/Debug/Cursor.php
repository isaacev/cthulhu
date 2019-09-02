<?php

namespace Cthulhu\Debug;

class Cursor {
  protected $buffer = '';
  protected $properties = [];
  protected $is_reset = false;
  protected $spaces_after_newline = 0;

  function __construct(int $spaces_after_newline = 0) {
    $this->reset();
    $this->spaces_after_newline = $spaces_after_newline;
  }

  protected function set_prop(string $name, int $code): self {
    $this->properties[$name] = $code;
    return $this;
  }

  protected function unset_prop(string $name): self {
    unset($this->properties[$name]);
    return $this;
  }

  protected function commit_formatting(): void {
    $codes = array_values($this->properties);
    if (empty($codes)) {
      return;
    }

    $this->is_reset = false;
    $this->buffer .= "\033[" . implode(';', $codes) . 'm';
  }

  public function repeat(string $str, int $num): self {
    return $this->text(str_repeat($str, $num));
  }

  public function spaces(int $num): self {
    return $this->repeat(' ', $num);
  }

  public function bold(bool $is_set = true): self {
    if ($is_set) {
      return $this->set_prop('bold', 1);
    } else {
      return $this->unset_prop('bold');
    }
  }

  public function inverse(bool $is_set = true): self {
    if ($is_set) {
      return $this->set_prop('inverse', 7);
    } else {
      return $this->unset_prop('inverse');
    }
  }

  public function foreground(string $name): self {
    switch ($name) {
      case 'black':
        return $this->set_prop('foreground', 30);
      case 'red':
        return $this->set_prop('foreground', 31);
      case 'green':
        return $this->set_prop('foreground', 32);
      case 'yellow':
        return $this->set_prop('foreground', 33);
      case 'blue':
        return $this->set_prop('foreground', 34);
      case 'magenta':
        return $this->set_prop('foreground', 35);
      case 'cyan':
        return $this->set_prop('foreground', 36);
      case 'white':
        return $this->set_prop('foreground', 37);
      default:
        throw new \Exception("unknown ANSI color: '$name'");
    }
  }

  public function background(string $name): self {
    switch ($name) {
      case 'black':
        return $this->set_prop('background', 40);
      case 'red':
        return $this->set_prop('background', 41);
      case 'green':
        return $this->set_prop('background', 42);
      case 'yellow':
        return $this->set_prop('background', 43);
      case 'blue':
        return $this->set_prop('background', 44);
      case 'magenta':
        return $this->set_prop('background', 45);
      case 'cyan':
        return $this->set_prop('background', 46);
      case 'white':
        return $this->set_prop('background', 47);
      default:
        throw new \Exception("unknown ANSI color: '$name'");
    }
  }

  public function sprintf(string $format, ...$args): self {
    $this->commit_formatting();
    $this->buffer .= \sprintf($format, ...$args);
    return $this;
  }

  public function reset(): self {
    $this->properties = [];
    if ($this->is_reset === false) {
      $this->is_reset = true;
      $this->buffer .= "\033[0m";
    }
    return $this;
  }

  public function text(string $text): self {
    $this->commit_formatting();
    $this->buffer .= $text;
    return $this;
  }

  public function newline(): self {
    return $this->text("\n");
  }

  public function __toString(): string {
    if ($this->is_reset === false) {
      $this->reset();
    }
    return $this->buffer;
  }
}
