# Test Organization Structure

This document describes the organized test structure for the Laravel e-commerce application.

## Directory Structure

```
tests/
├── admin/                          # Admin panel tests
│   ├── resources/                  # Filament Resource tests (40+ files)
│   └── widgets/                    # Admin widget tests (20+ files)
├── frontend/                       # Frontend tests
│   ├── features/                   # Frontend feature tests
│   └── admin/                      # Frontend admin tests (7 files)
├── api/                           # API tests
│   └── endpoints/                  # API endpoint tests (1 file)
├── models/                        # Model tests
│   ├── core/                      # Core model tests (60+ files)
│   ├── business/                  # Business model tests
│   └── relationships/             # Model relationship tests
├── services/                      # Service tests
│   └── business/                  # Business service tests
├── browser/                       # Browser/Dusk tests
│   ├── admin/                     # Admin panel browser tests
│   ├── frontend/                  # Frontend browser tests
│   └── integration/               # Integration browser tests
└── README.md                      # This file
```

## Test Categories

### Admin Tests (`tests/admin/`)
- **Resources**: Filament Resource tests for CRUD operations
- **Pages**: Admin page functionality tests
- **Widgets**: Admin dashboard widget tests
- **Actions**: Admin action tests (bulk operations, etc.)
- **Forms**: Admin form validation and submission tests
- **Tables**: Admin table functionality tests

### Frontend Tests (`tests/frontend/`)
- **Pages**: Frontend page tests (home, product, checkout, etc.)
- **Components**: Frontend component tests (product cards, forms, etc.)
- **Features**: Frontend feature tests (search, filtering, cart, etc.)
- **Integration**: Frontend integration tests

### API Tests (`tests/api/`)
- **Endpoints**: API endpoint tests
- **Auth**: API authentication and authorization tests
- **Validation**: API request/response validation tests

### Model Tests (`tests/models/`)
- **Core**: Core application models (User, System settings, etc.)
- **Business**: Business domain models (Product, Order, Category, etc.)
- **Relationships**: Model relationship and association tests

### Service Tests (`tests/services/`)
- **Business**: Business logic service tests
- **Integration**: Service integration tests
- **External**: External service integration tests

### Browser Tests (`tests/browser/`)
- **Admin**: Admin panel browser automation tests
- **Frontend**: Frontend browser automation tests
- **Integration**: End-to-end browser tests

## Running Tests

### Run all tests
```bash
php artisan test
```

### Run specific test categories
```bash
# Admin tests
php artisan test tests/admin/

# Frontend tests
php artisan test tests/frontend/

# API tests
php artisan test tests/api/

# Model tests
php artisan test tests/models/

# Service tests
php artisan test tests/services/

# Browser tests
php artisan dusk tests/browser/
```

### Run specific test groups
```bash
# Admin resources only
php artisan test tests/admin/resources/

# Frontend features only
php artisan test tests/frontend/features/

# Business models only
php artisan test tests/models/business/
```

## Test Naming Conventions

- **Feature Tests**: `*Test.php` (e.g., `ProductResourceTest.php`)
- **Unit Tests**: `*Test.php` (e.g., `UserModelTest.php`)
- **Browser Tests**: `*Test.php` (e.g., `AdminPanelTest.php`)

## Test Organization Principles

1. **Separation of Concerns**: Tests are organized by functionality and domain
2. **Logical Grouping**: Related tests are grouped together
3. **Clear Hierarchy**: Tests follow a clear directory structure
4. **Easy Navigation**: Developers can quickly find relevant tests
5. **Maintainability**: Easy to maintain and update test structure

## Migration Notes

This structure was created by reorganizing the existing test files from the previous flat structure. All existing tests have been moved to their appropriate locations based on their functionality and domain.

### Previous Structure
- `tests/Feature/` → Moved to appropriate categories
- `tests/Unit/` → Moved to `tests/models/` and `tests/services/`
- `tests/Browser/` → Moved to `tests/browser/` with subcategories

### Benefits of New Structure
- **Better Organization**: Tests are logically grouped
- **Easier Maintenance**: Related tests are together
- **Clearer Purpose**: Each directory has a specific purpose
- **Scalability**: Easy to add new test categories
- **Team Collaboration**: Easier for team members to find and work with tests