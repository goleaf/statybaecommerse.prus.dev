#!/bin/bash

# Реалтайм автофикс система для Laravel 11 + Filament v4
# Автоматически запускает все проверки и исправления при изменении файлов

set -e

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Функция логирования
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

# Функция проверки синтаксиса
check_syntax() {
    local file="$1"
    log "Проверка синтаксиса: $file"
    
    if php -l "$file" > /dev/null 2>&1; then
        success "Синтаксис OK: $file"
        return 0
    else
        error "Синтаксическая ошибка в: $file"
        php -l "$file"
        return 1
    fi
}

# Функция исправления стиля
fix_style() {
    local file="$1"
    log "Исправление стиля: $file"
    
    if vendor/bin/pint "$file" --test > /dev/null 2>&1; then
        success "Стиль OK: $file"
        return 0
    else
        log "Применение исправлений стиля: $file"
        vendor/bin/pint "$file"
        success "Стиль исправлен: $file"
        return 0
    fi
}

# Функция статического анализа
analyze_code() {
    local file="$1"
    log "Статический анализ: $file"
    
    if vendor/bin/phpstan analyse "$file" -c phpstan.neon --memory-limit=1G --no-progress > /dev/null 2>&1; then
        success "Анализ OK: $file"
        return 0
    else
        warning "Предупреждения в: $file"
        vendor/bin/phpstan analyse "$file" -c phpstan.neon --memory-limit=1G --no-progress
        return 1
    fi
}

# Функция авторефакторинга
refactor_code() {
    local file="$1"
    log "Авторефакторинг: $file"
    
    if vendor/bin/rector process "$file" --ansi --no-progress-bar > /dev/null 2>&1; then
        success "Рефакторинг OK: $file"
        return 0
    else
        log "Применение рефакторинга: $file"
        vendor/bin/rector process "$file" --ansi --no-progress-bar
        success "Рефакторинг применён: $file"
        return 0
    fi
}

# Функция обновления кэшей
update_caches() {
    log "Обновление кэшей Laravel"
    
    php artisan config:clear > /dev/null 2>&1
    php artisan route:clear > /dev/null 2>&1
    php artisan view:clear > /dev/null 2>&1
    
    php artisan config:cache > /dev/null 2>&1
    php artisan route:cache > /dev/null 2>&1
    php artisan view:cache > /dev/null 2>&1
    
    success "Кэши обновлены"
}

# Функция запуска тестов
run_tests() {
    local test_name="$1"
    log "Запуск тестов: $test_name"
    
    if [ -n "$test_name" ]; then
        php artisan test --filter="$test_name" --stop-on-failure
    else
        php artisan test --stop-on-failure
    fi
    
    success "Тесты выполнены"
}

# Функция обработки файла
process_file() {
    local file="$1"
    local max_attempts=5
    local attempt=1
    
    log "Обработка файла: $file"
    
    while [ $attempt -le $max_attempts ]; do
        log "Попытка $attempt из $max_attempts"
        
        # Проверка синтаксиса
        if ! check_syntax "$file"; then
            error "Критическая ошибка синтаксиса в: $file"
            return 1
        fi
        
        # Исправление стиля
        fix_style "$file"
        
        # Статический анализ
        analyze_code "$file"
        
        # Авторефакторинг
        refactor_code "$file"
        
        # Если все проверки прошли успешно, выходим
        if check_syntax "$file" && vendor/bin/pint "$file" --test > /dev/null 2>&1; then
            success "Файл успешно обработан: $file"
            return 0
        fi
        
        attempt=$((attempt + 1))
    done
    
    error "Не удалось исправить все ошибки в: $file"
    return 1
}

# Функция мониторинга файлов
monitor_files() {
    log "Запуск мониторинга файлов"
    
    # Используем inotifywait для мониторинга изменений
    if command -v inotifywait > /dev/null 2>&1; then
        inotifywait -m -r -e modify,create,delete \
            --exclude '(vendor/|node_modules/|\.git/|storage/|bootstrap/cache/)' \
            app/ database/ config/ routes/ tests/ resources/views/ |
        while read path action file; do
            if [[ "$file" =~ \.(php|blade\.php)$ ]]; then
                full_path="$path$file"
                log "Обнаружено изменение: $full_path"
                process_file "$full_path"
            fi
        done
    else
        warning "inotifywait не установлен. Установите: apt-get install inotify-tools"
        log "Запуск в режиме опроса каждые 5 секунд"
        
        while true; do
            find app/ database/ config/ routes/ tests/ resources/views/ \
                -name "*.php" -o -name "*.blade.php" | \
            while read file; do
                if [ -f "$file" ] && [ "$file" -nt /tmp/last_check ]; then
                    process_file "$file"
                    touch /tmp/last_check
                fi
            done
            sleep 5
        done
    fi
}

# Основная функция
main() {
    log "Запуск реалтайм автофикс системы"
    
    # Проверка зависимостей
    if [ ! -f "vendor/bin/pint" ]; then
        error "Laravel Pint не установлен. Запустите: composer install"
        exit 1
    fi
    
    if [ ! -f "vendor/bin/phpstan" ]; then
        error "PHPStan не установлен. Запустите: composer install"
        exit 1
    fi
    
    if [ ! -f "vendor/bin/rector" ]; then
        error "Rector не установлен. Запустите: composer install"
        exit 1
    fi
    
    # Создание временного файла для отслеживания
    touch /tmp/last_check
    
    # Обновление кэшей
    update_caches
    
    # Запуск мониторинга
    monitor_files
}

# Обработка сигналов
trap 'log "Остановка мониторинга..."; exit 0' INT TERM

# Запуск основной функции
main "$@"
