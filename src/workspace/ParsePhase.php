<?php

namespace Cthulhu\workspace;

use Cthulhu\Errors\Error;
use Cthulhu\Parser\Parser;
use Cthulhu\Source\File;

class ParsePhase {
  private File $file;

  function __construct(File $file) {
    $this->file = $file;
  }

  /**
   * @return LinkPhase
   * @throws Error
   */
  function parse(): LinkPhase {
    $syntax_tree = Parser::file_to_ast($this->file);
    return new LinkPhase($syntax_tree);
  }
}
