<?php

namespace Cthulhu\utils\test;

use \Cthulhu\Analysis;
use \Cthulhu\Codegen;
use \Cthulhu\Errors;
use \Cthulhu\Parser;
use \Cthulhu\Source;

class Test {
  public $dir;
  public $name;
  public $input;
  public $expected;

  function __construct(string $dir, string $name, string $input, TestOutput $expected) {
    $this->dir = $dir;
    $this->name = $name;
    $this->input = $input;
    $this->expected = $expected;
  }

  public function run(): TestResult {
    $found = $this->eval();

    if ($this->expected->equals($found)) {
      return new TestPassed($this);
    }

    return new TestFailed($this, $found);
  }

  public function bless(TestOutput $blessed_output): void {
    $stdout_filename = realpath("$this->dir/$this->name.stdout");
    if ($stdout_filename !== false) {
      $stdout_file = fopen($stdout_filename, 'w');
      fwrite($stdout_file, $blessed_output->stdout);
      fclose($stdout_file);
    }

    $stderr_filename = realpath("$this->dir/$this->name.stderr");
    if ($stderr_filename !== false) {
      $stderr_file = fopen($stderr_filename, 'w');
      fwrite($stderr_file, $blessed_output->stderr);
      fclose($stderr_file);
    }
  }

  protected function eval(): TestOutput {
    try {
      $file   = new Source\File($this->name, $this->input);
      $ast    = Parser\Parser::file_to_ast($file);
      $mod    = Analysis\Analyzer::ast_to_module($ast);
      $sym    = $mod->scope->to_symbol('main');
      $ref    = Codegen\PHP\Reference::from_symbol($sym);
      $php    = Codegen\Codegen::generate($mod, $ref);
      $stdout = $php->build()->write(new Codegen\StringWriter());
      return new TestOutput($stdout, '');
    } catch (Errors\Error $err) {
      $stderr = new \Cthulhu\utils\fmt\StringFormatter();
      $err->format($stderr);
      return new TestOutput('', $stderr);
    }
  }
}
