<?php

namespace Cthulhu\ast\nodes;

class FnItem extends Item {
  public FnName $name;
  public FnParams $params;
  public Note $returns;
  public BlockNode $body;

  /**
   * @param FnName    $name
   * @param FnParams  $params
   * @param Note      $returns
   * @param BlockNode $body
   */
  public function __construct($name, FnParams $params, Note $returns, BlockNode $body) {
    parent::__construct();
    $this->name    = $name;
    $this->params  = $params;
    $this->returns = $returns;
    $this->body    = $body;
  }

  public function children(): array {
    return [ $this->name, $this->params, $this->returns, $this->body ];
  }
}
