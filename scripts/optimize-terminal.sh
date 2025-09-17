#!/bin/bash

# Terminal Optimization Script for Laravel 11 + Filament v4
# Run this script whenever you experience terminal freezing

set -e

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${BLUE}[$(date +'%H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Quick fixes
quick_fix() {
    log "Applying quick terminal fixes..."
    
    # Kill hanging processes
    pkill -f "autofix-realtime.sh" || true
    pkill -f "inotifywait" || true
    
    # Clear PHP opcache
    php -r "if (function_exists('opcache_reset')) { opcache_reset(); }"
    
    # Clear Laravel caches
    php artisan config:clear > /dev/null 2>&1 || true
    php artisan route:clear > /dev/null 2>&1 || true
    php artisan view:clear > /dev/null 2>&1 || true
    
    # Rebuild optimized caches
    php artisan config:cache > /dev/null 2>&1 || true
    php artisan route:cache > /dev/null 2>&1 || true
    
    success "Quick fixes applied"
}

# Memory cleanup
cleanup_memory() {
    log "Cleaning up memory..."
    
    # Clear system caches
    sync
    echo 1 > /proc/sys/vm/drop_caches 2>/dev/null || true
    
    # Clear composer cache
    composer clear-cache > /dev/null 2>&1 || true
    
    # Clear npm cache
    npm cache clean --force > /dev/null 2>&1 || true
    
    success "Memory cleanup completed"
}

# Test terminal responsiveness
test_responsiveness() {
    log "Testing terminal responsiveness..."
    
    local start_time=$(date +%s)
    
    # Test basic commands
    php --version > /dev/null 2>&1
    composer --version > /dev/null 2>&1
    npm --version > /dev/null 2>&1
    php artisan --version > /dev/null 2>&1
    
    local end_time=$(date +%s)
    local duration=$((end_time - start_time))
    
    if [ $duration -le 10 ]; then
        success "Terminal responsiveness: EXCELLENT (${duration}s)"
    elif [ $duration -le 20 ]; then
        warning "Terminal responsiveness: GOOD (${duration}s)"
    else
        error "Terminal responsiveness: SLOW (${duration}s)"
    fi
}

# Main function
main() {
    log "Starting terminal optimization..."
    
    quick_fix
    cleanup_memory
    test_responsiveness
    
    success "Terminal optimization completed!"
    log "Your terminal should now be more responsive."
    
    echo
    log "Available optimization scripts:"
    echo "  - ./optimize-terminal.sh    (this script - quick fixes)"
    echo "  - ./fix-terminal-freezing.sh (comprehensive fix)"
    echo "  - ./terminal-test.sh        (test terminal performance)"
    echo "  - ./autofix-optimized.sh    (optimized autofix monitoring)"
}

# Run optimization
main "$@"
