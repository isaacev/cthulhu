<?php

namespace Cthulhu\php;

use Cthulhu\val\IntegerValue;

class Helpers {
  /**
   * @param string          $helper_name
   * @param nodes\Reference $runtime_namespace
   * @return nodes\FuncStmt
   */
  public static function get(string $helper_name, nodes\Reference $runtime_namespace): nodes\FuncStmt {
    switch ($helper_name) {
      case 'curry':
        return self::get_curry_helper($runtime_namespace);
      default:
        die("no helper named '$helper_name'\n");
    }
  }

  public static function get_curry_helper(nodes\Reference $runtime_namespace): nodes\FuncStmt {
    $fn             = new nodes\Variable('fn', new names\Symbol());
    $argv           = new nodes\Variable('argv', new names\Symbol());
    $arity          = new nodes\Variable('arity', new names\Symbol());
    $argc           = new nodes\Variable('argc', new names\Symbol());
    $reflect_func   = new nodes\Reference('ReflectionFunction', new names\Symbol);
    $get_num_params = new nodes\Variable('getNumberOfParameters', new names\Symbol());
    $count          = new nodes\Reference('count', new names\Symbol());
    $more_argv      = new nodes\Variable('more_argv', new names\Symbol());
    $array_merge    = new nodes\Reference('array_merge', new names\Symbol());
    $result         = new nodes\Variable('result', new names\Symbol());
    $is_callable    = new nodes\Reference('is_callable', new names\Symbol());
    $array_splice   = new nodes\Reference('array_splice', new names\Symbol());

    $head = new nodes\FuncHead(
      new nodes\Name('curry', ($curry_symbol = new names\Symbol())),
      [ $fn, $argv ]
    );

    $self = new nodes\Reference($runtime_namespace->segments . '\\curry', $head->name->symbol);

    $body = new nodes\BlockNode(
    // $arity = (new \ReflectionFunction($fn))->getNumberOfParameters();
      new nodes\AssignStmt(
        $arity,
        new nodes\CallExpr(
          new nodes\PropertyAccessExpr(
            new nodes\NewExpr(
              new nodes\ReferenceExpr($reflect_func, false),
              [ $fn ]),
            $get_num_params),
          []),
        // $argc = count($argv);
        new nodes\AssignStmt(
          $argc,
          new nodes\CallExpr(
            new nodes\ReferenceExpr($count, false),
            [ $argv ]),

          // $argc < $arity
          new nodes\IfStmt(
            new nodes\BinaryExpr(
              '<',
              new nodes\VariableExpr($argc),
              new nodes\VariableExpr($arity)),
            new nodes\BlockNode(
            // return \runtime\curry($fn, array_merge($argv, $more_argv));
              new nodes\ReturnStmt(new nodes\ArrowExpr([
                new nodes\FuncParam(true, $more_argv),
              ], new nodes\CallExpr(new nodes\ReferenceExpr($self, false), [
                new nodes\VariableExpr($fn),
                new nodes\CallExpr(new nodes\ReferenceExpr($array_merge, false), [
                  new nodes\VariableExpr($argv),
                  new nodes\VariableExpr($more_argv),
                ]),
              ])),
                null),
            ),

            // $argc === $arity
            new nodes\IfStmt(
              new nodes\BinaryExpr(
                '===',
                new nodes\VariableExpr($argc),
                new nodes\VariableExpr($arity)),
              new nodes\BlockNode(

              // $result = $fn(...$argv);
                new nodes\AssignStmt(
                  $result,
                  new nodes\CallExpr(
                    new nodes\VariableExpr($fn),
                    [ new nodes\UnaryExpr('...', new nodes\VariableExpr($argv)) ]),

                  // return \is_callable($result) ? curry($result, []) : $result;
                  new nodes\ReturnStmt(
                    new nodes\TernaryExpr(
                      new nodes\CallExpr(
                        new nodes\ReferenceExpr($is_callable, false),
                        [
                          new nodes\VariableExpr($result),
                        ]),
                      new nodes\CallExpr(
                        new nodes\ReferenceExpr($self, false),
                        [
                          new nodes\VariableExpr($result),
                          new nodes\OrderedArrayExpr([]),
                        ]),
                      new nodes\VariableExpr($result)
                    ),
                    null)),
              ),
              new nodes\BlockNode(

              // return \runtime\curry($fn(...\array_splice($argv, 0, $arity)), $argv);
                new nodes\ReturnStmt(
                  new nodes\CallExpr(new nodes\ReferenceExpr($self, false), [
                    new nodes\CallExpr(new nodes\VariableExpr($fn), [
                      new nodes\UnaryExpr(
                        '...',
                        new nodes\CallExpr(
                          new nodes\ReferenceExpr($array_splice, false),
                          [
                            new nodes\VariableExpr($argv),
                            new nodes\IntLiteral(IntegerValue::from_scalar(0)),
                            new nodes\VariableExpr($arity),
                          ]
                        )
                      ),
                    ]),
                    new nodes\VariableExpr($argv),
                  ]), null
                ),
              ),
              null
            ),
            null)
        ))

    );

    return new nodes\FuncStmt($head, $body, [], null);
  }
}
