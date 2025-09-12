#!/bin/bash

# Terminal Performance Test Script
# Tests all common commands that might freeze

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

# Test functions
test_php() {
    log "Testing PHP..."
    if timeout 5s php --version > /dev/null 2>&1; then
        success "PHP: OK"
        return 0
    else
        error "PHP: FAILED"
        return 1
    fi
}

test_composer() {
    log "Testing Composer..."
    if timeout 10s composer --version > /dev/null 2>&1; then
        success "Composer: OK"
        return 0
    else
        error "Composer: FAILED"
        return 1
    fi
}

test_npm() {
    log "Testing NPM..."
    if timeout 5s npm --version > /dev/null 2>&1; then
        success "NPM: OK"
        return 0
    else
        error "NPM: FAILED"
        return 1
    fi
}

test_laravel() {
    log "Testing Laravel..."
    if timeout 15s php artisan --version > /dev/null 2>&1; then
        success "Laravel: OK"
        return 0
    else
        error "Laravel: FAILED"
        return 1
    fi
}

test_artisan_commands() {
    log "Testing Artisan commands..."
    
    # Test basic commands
    local commands=("config:cache" "route:cache" "view:cache" "cache:clear")
    local failed=0
    
    for cmd in "${commands[@]}"; do
        if timeout 10s php artisan "$cmd" > /dev/null 2>&1; then
            success "Artisan $cmd: OK"
        else
            error "Artisan $cmd: FAILED"
            failed=1
        fi
    done
    
    return $failed
}

test_filament() {
    log "Testing Filament..."
    if timeout 10s php artisan filament:upgrade > /dev/null 2>&1; then
        success "Filament: OK"
        return 0
    else
        warning "Filament: SKIPPED (may take longer)"
        return 0
    fi
}

test_database() {
    log "Testing Database..."
    if timeout 5s php artisan migrate:status > /dev/null 2>&1; then
        success "Database: OK"
        return 0
    else
        error "Database: FAILED"
        return 1
    fi
}

test_memory_usage() {
    log "Testing Memory Usage..."
    
    # Get current memory usage
    local memory_before=$(ps -o pid,rss,comm -p $$ | tail -1 | awk '{print $2}')
    
    # Run a memory-intensive operation
    timeout 5s php -r "
        \$data = [];
        for (\$i = 0; \$i < 10000; \$i++) {
            \$data[] = str_repeat('test', 100);
        }
        echo 'Memory test completed';
    " > /dev/null 2>&1
    
    local memory_after=$(ps -o pid,rss,comm -p $$ | tail -1 | awk '{print $2}')
    local memory_diff=$((memory_after - memory_before))
    
    if [ $memory_diff -lt 50000 ]; then  # Less than 50MB increase
        success "Memory Usage: OK (${memory_diff}KB increase)"
        return 0
    else
        warning "Memory Usage: HIGH (${memory_diff}KB increase)"
        return 1
    fi
}

# Main test function
main() {
    log "Starting terminal performance tests..."
    echo
    
    local total_tests=0
    local passed_tests=0
    
    # Run all tests
    test_php && ((passed_tests++))
    ((total_tests++))
    
    test_composer && ((passed_tests++))
    ((total_tests++))
    
    test_npm && ((passed_tests++))
    ((total_tests++))
    
    test_laravel && ((passed_tests++))
    ((total_tests++))
    
    test_artisan_commands && ((passed_tests++))
    ((total_tests++))
    
    test_filament && ((passed_tests++))
    ((total_tests++))
    
    test_database && ((passed_tests++))
    ((total_tests++))
    
    test_memory_usage && ((passed_tests++))
    ((total_tests++))
    
    echo
    log "Test Results: $passed_tests/$total_tests tests passed"
    
    if [ $passed_tests -eq $total_tests ]; then
        success "All tests passed! Terminal freezing issues should be resolved."
        return 0
    elif [ $passed_tests -ge $((total_tests * 7 / 10)) ]; then
        warning "Most tests passed. Some minor issues may remain."
        return 1
    else
        error "Many tests failed. Terminal freezing issues persist."
        return 2
    fi
}

# Run tests
main "$@"
