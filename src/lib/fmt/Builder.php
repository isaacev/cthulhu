<?php

namespace Cthulhu\lib\fmt;

abstract class Builder implements Buildable {
  protected array $frames = [];

  protected function push_frame(callable $frame): self {
    array_push($this->frames, $frame);
    return $this;
  }

  protected function push_str(string $str): self {
    return $this->push_frame(function (Formatter $f) use ($str) {
      $f->print($str);
    });
  }

  protected function push_builder(Builder $builder): self {
    $this->frames = array_merge($this->frames, $builder->frames);
    return $this;
  }

  public function write(Formatter $f): Formatter {
    foreach ($this->frames as $frame) {
      $frame($f);
    }
    return $f;
  }

  public function build(): Builder {
    return $this;
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
