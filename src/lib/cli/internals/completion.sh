
#
# BEGIN CTHULHU COMPLETION
#
# Installation: cthulhu __complete >> ~/.bashrc (or ~/.zshrc)
#

if type complete &>/dev/null; then
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
elif type compdef &>/dev/null; then
  _complete_cthulhu() {
    local si=$IFS
    compadd -- $(COMP_CWORD=$((CURRENT-1)) \
                 COMP_LINE=$BUFFER \
                 COMP_POINT=0 \
                 cthulhu __complete -- "${words[@]}" \
                 2>/dev/null)
    IFS=$si
  }
  compdef _complete_cthulhu cthulhu
fi

#
# END CTHULHU COMPLETION
#
