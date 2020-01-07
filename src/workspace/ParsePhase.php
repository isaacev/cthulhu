<?php

namespace Cthulhu\workspace;

use Cthulhu\ast\Lexer;
use Cthulhu\ast\Parser;
use Cthulhu\ast\Scanner;
use Cthulhu\err\Error;
use Cthulhu\loc\File;

class ParsePhase {
  private File $file;

  public function __construct(File $file) {
    $this->file = $file;
  }

  /**
   * @return LinkPhase
   * @throws Error
   */
  public function parse(): LinkPhase {
    $scanner     = new Scanner($this->file);
    $lexer       = new Lexer($scanner);
    $parser      = new Parser($lexer);
    $syntax_tree = $parser->file();
    return new LinkPhase($syntax_tree);
  }
}
