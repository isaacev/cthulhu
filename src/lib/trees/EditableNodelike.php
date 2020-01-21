<?php

namespace Cthulhu\lib\trees;

interface EditableNodelike extends Nodelike {
  /**
   * @return EditableNodelike[]
   */
  public function children(): array;

  /**
   * @param EditableNodelike[] $children
   * @return EditableNodelike
   */
  public function from_children(array $children): EditableNodelike;
}

