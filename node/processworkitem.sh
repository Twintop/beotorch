#!/bin/bash

mkdir -p "$HOME/tmp"
PIDFILE="$HOME/tmp/beotorch.pid"

if [ -e "${PIDFILE}" ] && (ps -u $(whoami) -opid= |
                           grep -P "^\s*$(cat ${PIDFILE})$" &> /dev/null); then
  echo "Already running."
  exit 99
fi

(cd /home/currentuser/beotorch && perl processworkitem.pl) &

echo $! > "${PIDFILE}"
chmod 644 "${PIDFILE}"
