#!/bin/sh
# Install beside server_poll.py under ~/.mitsune-deploy, outside the web root.
set -eu
umask 077
DEPLOY_HOME="$HOME/.mitsune-deploy"
export PATH=/usr/bin:/bin:/usr/local/bin
cd "$DEPLOY_HOME"
# This outer lock also serializes log rotation.
exec 9>cron.lock
flock -n 9 || exit 0
if [ -f deploy.log ] && [ "$(wc -c < deploy.log)" -gt 2097152 ]; then
  mv -f deploy.log deploy.log.previous
fi
exec "$DEPLOY_HOME/venv/bin/python" "$DEPLOY_HOME/server_poll.py" --home "$DEPLOY_HOME" >> deploy.log 2>&1
