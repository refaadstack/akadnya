#!/bin/bash
# Helper script untuk MyAkad project
# Semua command PHP/composer/artisan dijalankan via Docker container "myakad-app"
# Usage dari WSL: bash /home/refaad/projects/myakad/docker.sh [command]

CONTAINER="myakad-app"
PROJECT_PATH="/app"

case "${1}" in
  php)
    shift
    docker exec $CONTAINER php "$@"
    ;;
  composer)
    shift
    docker exec $CONTAINER composer "$@"
    ;;
  artisan)
    shift
    docker exec $CONTAINER php artisan "$@"
    ;;
  pest)
    shift
    docker exec $CONTAINER ./vendor/bin/pest "$@"
    ;;
  pint)
    shift
    docker exec $CONTAINER ./vendor/bin/pint --dirty --format agent "$@"
    ;;
  npm)
    shift
    docker exec $CONTAINER npm "$@"
    ;;
  test)
    shift
    docker exec $CONTAINER ./vendor/bin/pest --compact "$@"
    ;;
  tinker)
    shift
    docker exec $CONTAINER php artisan tinker "$@"
    ;;
  migrate)
    shift
    docker exec $CONTAINER php artisan migrate "$@"
    ;;
  status)
    echo "=== MyAkad Docker Helper ==="
    echo "Container: $CONTAINER"
    docker exec $CONTAINER php -v 2>&1 | head -1
    docker exec $CONTAINER composer --version 2>&1 | head -1
    echo ""
    echo "Available commands: php, composer, artisan, pest, pint, npm, test, tinker, migrate, status"
    ;;
  *)
    echo "MyAkad Docker Helper"
    echo "Usage: bash docker.sh [php|composer|artisan|pest|pint|npm|test|tinker|migrate|status]"
    ;;
esac
