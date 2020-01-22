<?php

namespace Cthulhu\lib\trees;

interface HasSuccessor extends Nodelike {
  public function successor(): ?HasSuccessor;
}
