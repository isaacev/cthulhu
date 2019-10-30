<?php

namespace Cthulhu\workspace;

use Cthulhu\Parser\Parser;
use Cthulhu\Source\File;

class ParsePhase {
  private $file;

  function __construct(File $file) {
    $this->file = $file;
  }

  function parse(): LinkPhase {
    $syntax_tree = Parser::file_to_ast($this->file);
    return new LinkPhase($syntax_tree);
  }
}
