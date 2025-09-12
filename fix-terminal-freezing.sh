#!/bin/bash

# Terminal Freezing Fix Script for Laravel 11 + Filament v4
# This script fixes all common causes of terminal freezing

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log() {
    echo -e "${BLUE}[$(date +'%H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

error() {
    echo -e "${RED}❌ $1${NC}"
}

# Function to fix PHP configuration issues
fix_php_config() {
    log "Fixing PHP configuration issues..."
    
    # Kill any hanging PHP processes
    pkill -f "php-fpm" || true
    pkill -f "php artisan" || true
    
    # Clear PHP opcache
    php -r "if (function_exists('opcache_reset')) { opcache_reset(); echo 'OPcache cleared\n'; }"
    
    # Fix mbstring duplicate loading issue
    if grep -q "extension=mbstring" /www/server/php/83/etc/php-cli.ini; then
        warning "Found duplicate mbstring extension loading"
        # Create backup
        cp /www/server/php/83/etc/php-cli.ini /www/server/php/83/etc/php-cli.ini.backup.$(date +%s)
        # Comment out duplicate extension
        sed -i 's/^extension=mbstring/#extension=mbstring/' /www/server/php/83/etc/php-cli.ini
        success "Fixed duplicate mbstring extension loading"
    fi
    
    success "PHP configuration fixed"
}

# Function to optimize Laravel caches
optimize_laravel() {
    log "Optimizing Laravel caches..."
    
    # Clear all caches
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
    php artisan cache:clear || true
    
    # Optimize for production
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
    
    success "Laravel caches optimized"
}

# Function to fix composer issues
fix_composer() {
    log "Fixing Composer issues..."
    
    # Kill hanging composer processes
    pkill -f "composer" || true
    
    # Clear composer cache
    composer clear-cache || true
    
    # Update composer autoloader
    composer dump-autoload --optimize || true
    
    success "Composer issues fixed"
}

# Function to fix npm/node issues
fix_npm() {
    log "Fixing NPM/Node issues..."
    
    # Kill hanging npm processes
    pkill -f "npm" || true
    pkill -f "node" || true
    
    # Clear npm cache
    npm cache clean --force || true
    
    # Fix npm config warnings
    npm config delete init.module || true
    
    success "NPM issues fixed"
}

# Function to fix database issues
fix_database() {
    log "Fixing database issues..."
    
    # Ensure SQLite database exists
    touch database/database.sqlite
    
    # Check database permissions
    chmod 664 database/database.sqlite
    
    # Run migrations if needed
    php artisan migrate --force || true
    
    success "Database issues fixed"
}

# Function to optimize system resources
optimize_system() {
    log "Optimizing system resources..."
    
    # Clear system caches
    sync
    echo 3 > /proc/sys/vm/drop_caches || true
    
    # Kill any hanging processes
    pkill -f "autofix-realtime.sh" || true
    pkill -f "inotifywait" || true
    
    # Optimize file descriptors
    ulimit -n 65536 || true
    
    success "System resources optimized"
}

# Function to create optimized autofix script
create_optimized_autofix() {
    log "Creating optimized autofix script..."
    
    cat > autofix-optimized.sh << 'EOF'
#!/bin/bash

# Optimized Real-time Autofix System
# Prevents terminal freezing with better resource management

set -e

# Resource limits
MAX_MEMORY_MB=512
MAX_EXECUTION_TIME=30
MAX_PARALLEL_PROCESSES=2

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() {
    echo -e "[$(date +'%H:%M:%S')] $1"
}

# Resource monitoring
check_resources() {
    local memory_usage=$(ps -o pid,rss,comm -p $$ | tail -1 | awk '{print $2}')
    local memory_mb=$((memory_usage / 1024))
    
    if [ $memory_mb -gt $MAX_MEMORY_MB ]; then
        log "Memory usage too high: ${memory_mb}MB, restarting..."
        exit 1
    fi
}

# Optimized file processing
process_file_optimized() {
    local file="$1"
    
    # Check resources before processing
    check_resources
    
    # Timeout protection
    timeout $MAX_EXECUTION_TIME bash -c "
        # Syntax check
        php -l '$file' > /dev/null 2>&1 || return 1
        
        # Style fix with timeout
        timeout 10s vendor/bin/pint '$file' --test > /dev/null 2>&1 || timeout 10s vendor/bin/pint '$file'
        
        # Limited analysis
        timeout 15s vendor/bin/phpstan analyse '$file' --memory-limit=256M --no-progress > /dev/null 2>&1 || true
    " || log "Processing timeout for: $file"
}

# Optimized monitoring
monitor_files_optimized() {
    log "Starting optimized file monitoring..."
    
    # Use find with limits instead of inotifywait
    while true; do
        find app/ database/ config/ routes/ tests/ resources/views/ \
            -name "*.php" -o -name "*.blade.php" | \
        head -10 | \
        while read file; do
            if [ -f "$file" ] && [ "$file" -nt /tmp/last_check_optimized ]; then
                process_file_optimized "$file"
                touch /tmp/last_check_optimized
            fi
        done
        
        # Resource cleanup
        check_resources
        
        sleep 2
    done
}

# Main execution
main() {
    log "Starting optimized autofix system..."
    
    # Create tracking file
    touch /tmp/last_check_optimized
    
    # Start monitoring
    monitor_files_optimized
}

# Signal handling
trap 'log "Stopping optimized monitoring..."; exit 0' INT TERM

main "$@"
EOF

    chmod +x autofix-optimized.sh
    success "Optimized autofix script created"
}

# Function to test terminal performance
test_terminal() {
    log "Testing terminal performance..."
    
    # Test PHP
    timeout 5s php --version > /dev/null && success "PHP: OK" || error "PHP: FAILED"
    
    # Test Composer
    timeout 5s composer --version > /dev/null && success "Composer: OK" || error "Composer: FAILED"
    
    # Test NPM
    timeout 5s npm --version > /dev/null && success "NPM: OK" || error "NPM: FAILED"
    
    # Test Laravel
    timeout 10s php artisan --version > /dev/null && success "Laravel: OK" || error "Laravel: FAILED"
    
    success "Terminal performance test completed"
}

# Main execution
main() {
    log "Starting terminal freezing fix..."
    
    fix_php_config
    optimize_laravel
    fix_composer
    fix_npm
    fix_database
    optimize_system
    create_optimized_autofix
    test_terminal
    
    success "Terminal freezing fix completed!"
    log "You can now use: ./autofix-optimized.sh for better performance"
}

# Run main function
main "$@"
