<?php

namespace Cthulhu\ast\nodes;

class IntrinsicItem extends Item {
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
