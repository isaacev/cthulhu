<?php

namespace Cthulhu\lib\fmt;

abstract class Builder implements Buildable {
  public const CMD_WRITE_STR    = 1;
  public const CMD_INC_INDENT   = 2;
  public const CMD_DEC_INDENT   = 3;
  public const CMD_WRITE_INDENT = 4;
  public const CMD_APPLY_STYLES = 5;

  protected array $commands = [];
  protected int $total_instructions = 0;

  protected function push_cmd(int $code, ...$args): self {
    $this->total_instructions++;
    array_push($this->commands, $code, ...$args);
    return $this;
  }

  protected function push_str(string $str): self {
    return $this->push_cmd(self::CMD_WRITE_STR, $str);
  }

  public function increase_indentation(int $n = 2): self {
    return $this->push_cmd(self::CMD_INC_INDENT, $n);
  }

  public function decrease_indentation(): self {
    return $this->push_cmd(self::CMD_DEC_INDENT);
  }

  public function indent(): self {
    return $this->push_cmd(self::CMD_WRITE_INDENT);
  }

  public function apply_styles(int ...$styles): self {
    return $this->push_cmd(self::CMD_APPLY_STYLES, count($styles), ...$styles);
  }

  public function clear_styles(): self {
    return $this->apply_styles(Reset::ALL);
  }

  protected function push_builder(Builder $builder): self {
    $this->total_instructions += $builder->total_instructions;
    array_push($this->commands, ...$builder->commands);
    return $this;
  }

  protected function run(int $pc, Formatter $f): int {
    while ($pc < count($this->commands)) {
      switch ($this->commands[$pc++]) {
        case self::CMD_WRITE_STR:
          $f->print($this->commands[$pc++]);
          break;
        case self::CMD_INC_INDENT:
          $f->increment_tab_stop($this->commands[$pc++]);
          break;
        case self::CMD_DEC_INDENT:
          $f->pop_tab_stop();
          break;
        case self::CMD_WRITE_INDENT:
          $f->tab();
          break;
        case self::CMD_APPLY_STYLES:
          $total  = $this->commands[$pc++];
          $styles = array_slice($this->commands, $pc, $total);
          $pc     += $total;
          $f->apply_styles(...$styles);
          break;
        default:
          return $pc;
      }
    }
    return $pc;
  }

  public function write(Formatter $f): Formatter {
    $this->run(0, $f);
    return $f;
  }

  public function build(): Builder {
    return $this;
  }

  public function then(?Buildable $buildable): self {
    if ($buildable) {
      return $this->push_builder($buildable->build());
    } else {
      return $this;
    }
  }

  public function maybe(bool $test, Buildable $if_true): self {
    if ($test) {
      return $this->then($if_true);
    } else {
      return $this;
    }
  }

  public function each(array $buildables, ?Buildable $glue = null): self {
    foreach ($buildables as $i => $buildable) {
      if ($i > 0) {
        $this->then($glue);
      }
      $this->then($buildable);
    }
    return $this;
  }
}
