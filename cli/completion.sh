#/usr/bin/env bash

_complete_cthulhu() {
  local cur prev opts
  COMPREPLY=()
  cur="${COMP_WORDS[COMP_CWORD]}"
  prev="${COMP_WORDS[COMP_CWORD-1]}"

  if [[ ${COMP_CWORD} = 1 ]] ; then
    opts=$(cthulhu __complete)
    foundComps=$?
    if [ $foundComps -eq 0 ]; then
      COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
    else
      _filedir
    fi
    return 0
  fi

  if [[ ${COMP_CWORD} = 2 ]] ; then
    opts=$(cthulhu __complete $prev)
    foundComps=$?
    if [ $foundComps -eq 0 ]; then
      COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
    fi
    _filedir
    return 0
  fi

  COMPREPLY=( $(compgen -W "${opts}" -- ${cur}) )
  _filedir
  return 0;
}

complete -F _complete_cthulhu cthulhu
