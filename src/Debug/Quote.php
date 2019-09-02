<?php

namespace Cthulhu\Debug;

use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;

class Quote implements Reportable {
  protected $snippet;

  function __construct(Snippet $snippet) {
    $this->snippet = $snippet;
  }

  public function print(Cursor $cursor, ReportOptions $options): Cursor {
    $cursor->reset();

    /**
     * Glossary:
     *
     * - "focused region"
     *   The part of the program that is being brought to the user's attention.
     *   Characters within the focused region will have the underline character
     *   printed underneath for emphasis.
     *
     * - "visible region"
     *   The part of the program that will be printed. This includes the section
     *   of the program that is focused and few lines before and after that
     *   section for context.
     */

    $lines_above = $this->snippet->get_option('lines_above', 2);
    $lines_below = $this->snippet->get_option('lines_below', 2);
    $focus_color = $this->snippet->get_option('color', 'red');
    $focus_from = $this->snippet->location->from;
    $focus_to = $this->snippet->location->to;
    $first_focused_line = $focus_from->line;
    $last_focused_line = $focus_to->line;
    $all_tokens = Lexer::to_tokens($this->snippet->program, Lexer::MODE_RELAXED);

    if (empty($all_tokens)) {
      // TODO
      return $cursor
        ->text('empty program');
    }

    $first_visible_line_num = max(
      $first_focused_line - $lines_above,
      1);

    $last_visible_line_num = min(
      $last_focused_line + $lines_below,
      end($all_tokens)->span->to->line);

    // Group tokens by line in the visible region
    $visible_region_lines = [];
    foreach ($all_tokens as $token) {
      if ($token->span->to->line < $first_visible_line_num) {
        continue;
      } else if ($token->span->from->line > $last_visible_line_num) {
        break;
      }

      $token_line_num = $token->span->from->line;
      if (array_key_exists($token_line_num, $visible_region_lines) === false) {
        $visible_region_lines[$token_line_num] = [$token];
      } else {
        $visible_region_lines[$token_line_num][] = $token;
      }
    }

    $max_line_num_width = strlen(strval($last_visible_line_num));
    $gutter_padding_format = "  %${max_line_num_width}d | ";
    $gutter_focused_format = "> %${max_line_num_width}d | ";
    $gutter_underline_format = '  ' . str_repeat(' ', $max_line_num_width) . ' | ';

    foreach ($visible_region_lines as $line_num => $tokens_in_line) {
      $line_contains_focused_region = (
        $first_focused_line <= $line_num &&
        $last_focused_line >= $line_num
      );

      $cursor->spaces(2);

      if ($line_contains_focused_region) {
        // The line about to be printed includes some part of the focused region
        // so mark the line gutter with the `$gutter_focused_format`.
        $cursor
          ->foreground($focus_color)
          ->sprintf($gutter_focused_format, $line_num)
          ->reset();
      } else {
        // The line about to be printed does not contain any part of the focused
        // region and is only included for padding above or below that region.
        $cursor
          ->reset()
          ->sprintf($gutter_padding_format, $line_num);
      }

      // Print the source code tokens for this line
      $prev_col = 1;
      foreach ($tokens_in_line as $token) {
        $cursor->spaces($token->span->from->column - $prev_col);
        self::format_token($cursor, $token);
        $cursor->reset();
        $prev_col = $token->span->to->column;
      }

      $cursor->newline();

      // If the line doesn't contain any tokens skip the next step that draws an
      // underline below the focused region.
      if (empty($tokens_in_line)) {
        continue;
      }

      // If the line included some part of the focused region, print an
      // underline below the focused region on the next line.
      if ($line_contains_focused_region) {
        $cursor
          ->spaces(2)
          ->foreground($focus_color)
          ->sprintf($gutter_underline_format)
          ->reset();

        $first_token_on_line = $tokens_in_line[0];
        $last_token_on_line = end($tokens_in_line);

        // Compute number of spaces between the gutter and the start of the
        // underline characters.
        $spaces_width = ($line_num === $first_focused_line)
          ? $focus_from->column - 1
          : $first_token_on_line->span->from->column - 1;

        // Compute the number of underline characters to draw depending on where
        // the focus region falls on the line.
        $underline_width = ($line_num < $last_focused_line)
          ? $last_token_on_line->span->to->column - ($spaces_width + 1)
          : $focus_to->column - ($spaces_width + 1);

        $cursor
          ->spaces($spaces_width)
          ->foreground($focus_color)
          ->repeat('^', $underline_width)
          ->reset();

        // If this line is the last line of the focused region and if the
        // snippet contains a message string, print it now.
        if ($line_num === $last_focused_line) {
          $message = $this->snippet->get_option('message', null);
          if ($message !== null) {
            $cursor
              ->foreground($focus_color)
              ->sprintf(' %s', $message)
              ->reset();
          }
        }

        $cursor->newline();
      }
    }

    return $cursor;
  }

  private static function format_token(Cursor $cursor, Token $token): void {
    switch ($token->type) {
      case TokenType::ERROR:
        $cursor
          ->background('red')
          ->text($token->lexeme);
        break;
      case TokenType::LITERAL_NUM:
        $cursor
          ->foreground('magenta')
          ->text($token->lexeme);
        break;
      case TokenType::LITERAL_STR:
        $cursor
          ->foreground('green')
          ->text($token->lexeme);
        break;
      case TokenType::KEYWORD_USE:
      case TokenType::PLUS:
      case TokenType::DASH:
      case TokenType::STAR:
      case TokenType::EQUALS:
        $cursor
          ->foreground('yellow')
          ->text($token->lexeme);
        break;
      case TokenType::KEYWORD_FN:
      case TokenType::KEYWORD_LET:
      case TokenType::THIN_ARROW:
        $cursor
          ->foreground('cyan')
          ->text($token->lexeme);
        break;
      default:
        $cursor->text($token->lexeme);
    }
  }
}
