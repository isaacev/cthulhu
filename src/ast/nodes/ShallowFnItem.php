<?php

namespace Cthulhu\ast\nodes;

class ShallowFnItem extends ShallowItem {
  public FnName $name;
  public FnParams $params;
  public Note $returns;
  public ShallowBlock $body;

  public function __construct(FnName $name, FnParams $params, Note $returns, ShallowBlock $body) {
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
