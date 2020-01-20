<?php

namespace Cthulhu\ast\nodes;

class ShallowIntrinsicItem extends ShallowItem {
  public array $signatures;

  /**
   * @param IntrinsicSignature[] $signatures
   */
  public function __construct(array $signatures) {
    parent::__construct();
    $this->signatures = $signatures;
  }

  public function children(): array {
    return $this->signatures;
  }
}
