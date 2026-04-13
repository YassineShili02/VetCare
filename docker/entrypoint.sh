#!/bin/sh
set -e

nginx -g "daemon off;" &
NGINX_PID=$!

php-fpm --nodaemonize &
FPM_PID=$!

# wait -n is NOT supported in Alpine busybox
# This keeps the script alive and dies if either process dies
wait $NGINX_PID