#
# BEGIN CTHULHU COMPLETION
#
# Installation: cthulhu __complete >> ~/.bashrc (or ~/.zshrc)
#

_complete_cthulhu() {
  local cword words
  cword="$COMP_CWORD"
  words=("${COMP_WORDS[@]}")

  local si="$IFS"
  IFS=$'\n' COMPREPLY=($(COMP_CWORD="$cword" \
                         COMP_LINE="$COMP_LINE" \
                         COMP_POINT="$COMP_POINT" \
                         cthulhu __complete -- "${words[@]}" \
                         2>/dev/null)) || return $?
  IFS="$si"
}

complete -o default -F _complete_cthulhu cthulhu

#
# END CTHULHU COMPLETION
#
