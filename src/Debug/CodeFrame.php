<?php

namespace Cthulhu\Debug;

use Cthulhu\Parser\Lexer\Lexer;
use Cthulhu\Parser\Lexer\Point;
use Cthulhu\Parser\Lexer\Span;
use Cthulhu\Parser\Lexer\Token;
use Cthulhu\Parser\Lexer\TokenType;

class CodeFrame {
  public static function print(Snippet $snippet): void {
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

    $lines_above = $snippet->get_option('lines_above', 2);
    $lines_below = $snippet->get_option('lines_below', 2);
    $focus_color = $snippet->get_option('color', Color::Red);
    $focus_from = $snippet->location->from;
    $focus_to = $snippet->location->to;
    $first_focused_line = $focus_from->line;
    $last_focused_line = $focus_to->line;
    $all_tokens = Lexer::to_tokens($snippet->program);

    if (empty($all_tokens)) {
      // TODO
      echo 'empty program' . PHP_EOL;
      return;
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

      if ($line_contains_focused_region) {
        // The line about to be printed includes some part of the focused region
        // so mark the line gutter with the `$gutter_focused_format`.
        Color::printf($focus_color, $gutter_focused_format, $line_num);
      } else {
        // The line about to be printed does not contain any part of the focused
        // region and is only included for padding above or below that region.
        printf($gutter_padding_format, $line_num);
      }

      // Print the source code tokens for this line
      $prev_col = 1;
      foreach ($tokens_in_line as $token) {
        echo str_repeat(' ', $token->span->from->column - $prev_col);
        echo self::style_token($token);
        $prev_col = $token->span->to->column;
      }

      echo PHP_EOL;

      // If the line doesn't contain any tokens skip the next step that draws an
      // underline below the focused region.
      if (empty($tokens_in_line)) {
        continue;
      }

      // If the line included some part of the focused region, print an
      // underline below the focused region on the next line.
      if ($line_contains_focused_region) {
        Color::printf($focus_color, $gutter_underline_format);

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

        echo str_repeat(' ', $spaces_width);
        echo Color::str_repeat($focus_color, '^', $underline_width);

        // If this line is the last line of the focused region and if the
        // snippet contains a message string, print it now.
        if ($line_num === $last_focused_line) {
          $message = $snippet->get_option('message', null);
          if ($message !== null) {
            Color::printf($focus_color, ' %s', $message);
          }
        }

        echo PHP_EOL;
      }
    }
  }

  private static function style_token(Token $token): string {
    switch ($token->type) {
      case TokenType::LITERAL_NUM:
        return Color::sprintf(Color::LightSlateBlue, $token->lexeme);
      case TokenType::LITERAL_STR:
        return Color::sprintf(Color::DarkOliveGreen3, $token->lexeme);
      case TokenType::KEYWORD_USE:
      case TokenType::PLUS:
      case TokenType::DASH:
      case TokenType::STAR:
      case TokenType::EQUALS:
        return Color::sprintf(Color::IndianRed1, $token->lexeme);
      case TokenType::KEYWORD_FN:
      case TokenType::KEYWORD_LET:
      case TokenType::THIN_ARROW:
        return Color::sprintf(Color::SteelBlue1, $token->lexeme);
      default:
        return $token->lexeme;
    }
  }
}
