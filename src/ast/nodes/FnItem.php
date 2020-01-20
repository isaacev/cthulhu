<?php

namespace Cthulhu\ast\nodes;

class FnItem extends Item {
  public FnName $name;
  public array $params;
  public Note $returns;
  public BlockNode $body;

  /**
   * @param FnName      $name
   * @param ParamNode[] $params
   * @param Note        $returns
   * @param BlockNode   $body
   */
  public function __construct($name, array $params, Note $returns, BlockNode $body) {
    parent::__construct();
    $this->name    = $name;
    $this->params  = $params;
    $this->returns = $returns;
    $this->body    = $body;
  }

  public function children(): array {
    return array_merge(
      [ $this->name ],
      $this->params,
      [ $this->returns, $this->body ]
    );
  }
}
