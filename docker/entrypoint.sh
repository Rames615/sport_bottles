#!/bin/sh
set -e

# Railway injects $PORT at runtime. Default to 80 for local docker-compose runs.
export PORT="${PORT:-80}"

# Substitute ${PORT} in the nginx config template and write the live config.
envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf

# Hand off to supervisord (nginx + php-fpm).
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
