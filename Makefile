.PHONY: setup migrate seed test analyse format serve dev build clean

setup:
	composer install
	npm install
	@if [ ! -f .env ]; then cp .env.example .env; fi
	@mkdir -p database
	@if [ ! -f database/database.sqlite ]; then touch database/database.sqlite; fi
	php artisan key:generate --ansi
	php artisan storage:link || true

migrate:
	php artisan migrate --force --ansi

seed:
	php artisan db:seed --class=SimpleSystemSettingsSeeder --no-interaction --ansi
	php artisan db:seed --class=EnhancedFilamentSeeder --no-interaction --ansi || true
	@echo "Admin login: admin@statybaecommerse.prus.dev / admin123"
	@echo "If sample data factories are empty, rerun after importing products."
	@echo
	@echo "System settings seeded."

test:
	php artisan test --parallel --recreate-databases --stop-on-failure

analyse:
	vendor/bin/phpstan analyse -c phpstan.neon --memory-limit=1G --no-progress

format:
	vendor/bin/pint

serve:
	php artisan serve --host=127.0.0.1 --port=8000
	@echo "App available at http://127.0.0.1:8000"

dev:
	composer run dev

build:
	npm run build

clean:
	rm -rf vendor node_modules bootstrap/cache/*.php storage/framework/cache/* storage/framework/views/* storage/framework/sessions/*
	rm -f database/database.sqlite
	rm -f public/storage
	rm -f .env
