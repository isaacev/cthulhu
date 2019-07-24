<?php

namespace Cthulhu\Codegen;

class Writer {
  private $open = true;
  private $buffer = '';

  private function write(string $str): Writer {
    if ($this->open === false) {
      throw new \Exception('cannot write to closed Writer');
    }

    $this->buffer .= $str;
    return $this;
  }

  public function collect(): string {
    $this->open = false;
    return $this->buffer;
  }

  public function keyword(string $keyword): Writer {
    return $this->write($keyword);
  }

  public function name(string $name): Writer {
    return $this->write($name);
  }

  public function operator(string $operator): Writer {
    return $this->write($operator);
  }

  public function paren_left(): Writer {
    return $this->write('(');
  }

  public function paren_right(): Writer {
    return $this->write(')');
  }

  public function brace_left(): Writer {
    return $this->write('{');
  }

  public function brace_right(): Writer {
    return $this->write('}');
  }

  public function space(): Writer {
    return $this->write(' ');
  }

  public function variable(string $name): Writer {
    return $this->write('$' . $name);
  }

  public function comma(): Writer {
    return $this->write(',');
  }

  public function equals(): Writer {
    return $this->write('=');
  }

  public function semicolon(): Writer {
    return $this->write(';');
  }

  public function str(string $value): Writer {
    return $this->write('"' . $value . '"');
  }

  public function num(int $value): Writer {
    return $this->write("$value");
  }

  public function node(PHP\Node $node): Writer {
    return $node->write($this);
  }

  public function newline(): Writer {
    return $this->write("\n");
  }

  public function newline_separated(array $nodes): Writer {
    $writer = $this;
    for ($i = 0; $i < count($nodes); $i++) {
      $writer = $writer->node($nodes[$i]);
      if ($i < count($nodes) - 1) {
        $writer = $writer->newline();
      }
    }
    return $writer;
  }

  public function comma_separated(array $nodes): Writer {
    $writer = $this;
    for ($i = 0; $i < count($nodes); $i++) {
      $writer = $writer->node($nodes[$i]);
      if ($i < count($nodes) - 1) {
        $writer = $writer->comma();
      }
    }
    return $writer;
  }
}
