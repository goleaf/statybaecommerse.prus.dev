#!/usr/bin/env bash

set -euo pipefail

SCRIPT_DIR=$(cd -- "$(dirname "${BASH_SOURCE[0]}")" && pwd)
PROJECT_ROOT=$(cd -- "${SCRIPT_DIR}/.." && pwd)

cd "${PROJECT_ROOT}"

info() {
    printf '\033[0;34m[deploy]\033[0m %s\n' "$1"
}

success() {
    printf '\033[0;32m[deploy]\033[0m %s\n' "$1"
}

ensure_directories() {
    info "Ensuring cache directories exist"
    mkdir -p bootstrap/cache
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
}

clear_caches() {
    info "Clearing existing Laravel caches"
    php artisan config:clear >/dev/null 2>&1 || true
    php artisan route:clear >/dev/null 2>&1 || true
    php artisan view:clear >/dev/null 2>&1 || true
}

warm_caches() {
    info "Warming optimized Laravel caches"
    php artisan config:cache --no-ansi
    php artisan route:cache --no-ansi
    php artisan view:cache --no-ansi
}

main() {
    info "Starting production deploy bootstrap"

    ensure_directories
    clear_caches
    warm_caches

    success "Laravel caches primed for production"
}

main "$@"
