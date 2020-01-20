<?php

namespace Cthulhu\ast\nodes;

class ShallowFnItem extends ShallowItem {
  public FnName $name;
  public array $params;
  public Note $returns;
  public ShallowBlock $body;

  /**
   * @param FnName       $name
   * @param ParamNode[]  $params
   * @param Note         $returns
   * @param ShallowBlock $body
   */
  public function __construct(
    FnName $name,
    array $params,
    Note $returns,
    ShallowBlock $body
  ) {
    parent::__construct();
    $this->name    = $name;
    $this->params  = $params;
    $this->returns = $returns;
    $this->body    = $body;
  }

  public
  function children(): array {
    return array_merge(
      [ $this->name ],
      $this->params,
      [ $this->returns, $this->body ]
    );
  }
}
