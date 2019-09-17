#/usr/bin/env bash

_complete_cthulhu() {
  COMPREPLY=()
  words=$(cthulhu __complete -- "${COMP_WORDS[@]}")
  COMPREPLY=($(compgen -W "$words" -- "${COMP_WORDS[COMP_CWORD]}"))
  return 0
}

complete -F _complete_cthulhu cthulhu
