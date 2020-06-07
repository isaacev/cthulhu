<?php

namespace Cthulhu\err;

use Cthulhu\ast\Lexer;
use Cthulhu\ast\Scanner;
use Cthulhu\ast\ShallowParser;
use Cthulhu\ast\tokens;
use Cthulhu\lib\fmt\Background;
use Cthulhu\lib\fmt\Foreground;
use Cthulhu\lib\fmt\Formatter;
use Cthulhu\loc\File;
use Cthulhu\loc\Span;
use Cthulhu\loc\Spanlike;

class Snippet implements Reportable {
  public const LINES_ABOVE    = 0;
  public const LINES_BELOW    = 0;
  public const UNDERLINE_CHAR = '^';

  public File $file;
  public Span $location;
  public ?string $message;
  public array $options;

  public function __construct(File $file, Spanlike $location, ?string $message = null, array $options = []) {
    $this->file     = $file;
    $this->location = $location->span();
    $this->message  = $message;
    $this->options  = $options;
  }

  protected function get_option(string $name, $fallback) {
    if (array_key_exists($name, $this->options)) {
      return $this->options[$name];
    } else {
      return $fallback;
    }
  }

  public function print(Formatter $f): Formatter {
    $focus_from         = $this->location->from;
    $focus_to           = $this->location->to;
    $focus_color        = $this->get_option('color', Foreground::RED);
    $first_focused_line = $focus_from->line;
    $last_focused_line  = $focus_to->line;

    $all_tokens = self::all_tokens($this->location->from->file);

    if (empty($all_tokens)) {
      return $f
        ->newline_if_not_already()
        ->tab()
        ->print('empty program');
    }

    $is_multiline = $this->location->from->line !== $this->location->to->line;

    $first_visible_line_num = max(
      $first_focused_line - $this->get_option('lines_above', self::LINES_ABOVE),
      1);

    $last_visible_line_num = min(
      $last_focused_line + $this->get_option('lines_below', self::LINES_BELOW),
      end($all_tokens)->span->to->line);

    // Group tokens by line for each line in the visible region
    $visible_region_lines = [];
    $prev_line            = null;
    foreach ($all_tokens as $token) {
      $token_from_line = $token->span->from->line;
      $token_to_line   = $token->span->to->line;

      if ($token_to_line < $first_visible_line_num) {
        // Token ends before the visible region begins so skip this token
        continue;
      }

      if ($token_from_line > $last_visible_line_num) {
        // Token starts after the visible region. Since tokens are sequential,
        // this means that all subsequent tokens will also be outside of the
        // visible region so break the loop to skip this token and all subsequent
        // tokens.
        break;
      }

      // If there were blank lines between the last token and the current token,
      // add those blank lines to the `$token_from_line` table.
      $next_unallocated_line = $prev_line === null
        ? $token_from_line
        : $prev_line + 1;
      for ($line_num = $next_unallocated_line; $line_num <= $token_from_line; $line_num++) {
        $visible_region_lines[$line_num] = [];
      }

      $prev_line = $token_to_line;

      // Append the token to the list of other tokens from the same line
      $visible_region_lines[$token_from_line][] = $token;
    }

    // Determine how many columns will be needed in the gutter to print line
    // numbers. This is used to keep all the gutters aligned. The last visible
    // line is used because that will be the largest line number visible.
    $gutter_width = strlen(strval($last_visible_line_num));

    // Using the gutter width, build some formatting templates to be used when
    // printing each line of the quote.
    $gutter_padding_format   = "%${gutter_width}d | ";
    $gutter_focused_format   = "%${gutter_width}d | ";
    $gutter_underline_format = "%${gutter_width}s | ";

    $f->newline_if_not_already()
      ->tab()
      ->apply_styles(Foreground::WHITE)
      ->spaces($gutter_width)
      ->printf(' : %s', $this->file->filepath)
      ->reset_styles();

    // Now that the tokens have been filtered and grouped by line, iterate over
    // each line of tokens and print those tokens. If the line intersects with the
    // focused region, print an underline beneath the focused region.
    foreach ($visible_region_lines as $line_num => $tokens_in_line) {
      $line_contains_focused_region = (
        $first_focused_line <= $line_num &&
        $last_focused_line >= $line_num);

      $gutter_format = $line_contains_focused_region
        ? $gutter_focused_format
        : $gutter_padding_format;

      // Indent the line and print the gutter
      $f->newline_if_not_already()
        ->tab()
        ->apply_styles_if($line_contains_focused_region, $focus_color)
        ->printf($gutter_format, $line_num)
        ->reset_styles_if($line_contains_focused_region);

      if ($is_multiline) {
        // Print spaces to accommodate multiline bridge
        if ($line_contains_focused_region && $line_num > $first_focused_line) {
          $f->space()
            ->apply_styles($focus_color)
            ->print('|')
            ->reset_styles()
            ->space();
        } else {
          $f->spaces(3);
        }
      }

      // For each token in the line, check if that token has corresponding syntax
      // highlighting that can be applied to if. If so, apply those styles when
      // printing the token.
      $col = 1;
      foreach ($tokens_in_line as $token) {
        $styles     = self::token_styles($token);
        $has_styles = !empty($styles);
        $f->spaces($token->span->from->column - $col)
          ->apply_styles_if($has_styles, ...$styles)
          ->print($token->lexeme)
          ->reset_styles_if($has_styles);
        $col = $token->span->to->column;
      }

      if ($is_multiline) {
        if ($line_num === $first_focused_line || $line_num === $last_focused_line) {
          if ($line_num === $first_focused_line) {
            $slash  = '.';
            $to_col = $focus_from->column;
          } else {
            $slash  = "'";
            $to_col = max(0, $focus_to->column - 1);
          }

          $f->newline_if_not_already()
            ->tab()
            ->apply_styles($focus_color)
            ->printf($gutter_underline_format, ' ')
            ->spaces(1)
            ->print($slash)
            ->repeat('-', $to_col)
            ->print('^')
            ->reset_styles();
        }
      } else {
        // If the line contains some part of the focused region and has at least one
        // token, print an underline beneath the focused region.
        $line_has_tokens = !empty($tokens_in_line);
        if ($line_contains_focused_region && $line_has_tokens) {
          $f->newline_if_not_already()
            ->tab()
            ->apply_styles($focus_color)
            ->printf($gutter_underline_format, ' ');

          $first_token_on_line = $tokens_in_line[0];
          $last_token_on_line  = end($tokens_in_line);

          // Compute the number of spaces between the gutter and the start of the
          // underline characters.
          $total_spaces = ($line_num === $first_focused_line)
            ? $focus_from->column - 1
            : $first_token_on_line->span->from->column - 1;

          // Compute the number of underline characters depending on there the focus
          // region falls within the line.
          $total_underline = ($line_num < $last_focused_line)
            ? $last_token_on_line->span->to->column - ($total_spaces + 1)
            : $focus_to->column - ($total_spaces + 1);

          $f->spaces($total_spaces)
            ->repeat($this->get_option('underline', self::UNDERLINE_CHAR), $total_underline);

          // If this line contains the end of the focused region and if the note
          // includes a message, print that message after the underline.
          if ($line_num === $last_focused_line && $this->message) {
            $f->space()
              ->print($this->message);
          }

          $f->reset_styles();
        }
      }
    }

    return $f;
  }

  /**
   * @param Formatter      $f
   * @param tokens\Token[] $visible_region_lines
   * @return Formatter
   */
  private static function print_multiline(Formatter $f, array $visible_region_lines): Formatter {
    return $f;
  }

  public static function token_styles(tokens\Token $token): array {
    switch (true) {
      case $token instanceof tokens\ErrorToken:
        return [ Background::RED, Foreground::DEFAULT ];
      case $token instanceof tokens\StringToken:
        return [ Foreground::GREEN ];
      case $token instanceof tokens\LiteralToken:
      case $token instanceof tokens\IdentToken && $token->lexeme === 'true':
      case $token instanceof tokens\IdentToken && $token->lexeme === 'false':
        return [ Foreground::MAGENTA ];
      case $token instanceof tokens\PunctToken && $token->lexeme === ';':
      case $token instanceof tokens\CommentToken:
        return [ Foreground::BRIGHT_BLACK ];
      case $token instanceof tokens\PunctToken:
        return [ Foreground::YELLOW ];
      case $token instanceof tokens\IdentToken && in_array($token->lexeme, ShallowParser::RESERVED_WORDS):
        return [ Foreground::CYAN ];
      default:
        return [ Foreground::DEFAULT ];
    }
  }

  /**
   * @param File $file
   * @return tokens\Token[]
   */
  public static function all_tokens(File $file): array {
    $scanner = new Scanner($file);
    $lexer   = new Lexer($scanner, false, false);

    $tokens = [];
    try {
      while ($next = $tokens[] = $lexer->next()) {
        if ($next instanceof tokens\TerminalToken) {
          break;
        }
      }
    } catch (Error $err) {
      return $tokens;
    } finally {
      return $tokens;
    }
  }
}
