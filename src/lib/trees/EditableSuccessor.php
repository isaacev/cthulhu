<?php

namespace Cthulhu\lib\trees;

interface EditableSuccessor extends HasSuccessor, EditableNodelike {
  public function successor(): ?EditableSuccessor;

  public function from_successor(?EditableSuccessor $successor): EditableSuccessor;
}
