<?php

namespace Cthulhu\php;

class Helpers {
  /**
   * @param string          $helper_name
   * @param nodes\Reference $runtime_namespace
   * @return nodes\FuncStmt
   */
  static function get(string $helper_name, nodes\Reference $runtime_namespace): nodes\FuncStmt {
    switch ($helper_name) {
      default:
        die("no helper named '$helper_name'\n");
    }
  }
}
