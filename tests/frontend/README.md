# Test Organization

This directory contains organized test files grouped by functionality and type.

## Directory Structure

```
tests/Feature/
├── Admin/                    # Admin resource tests
│   ├── AdminResourcesTestSuite.php
│   ├── ProductResourceTest.php
│   ├── UserResourceTest.php
│   └── ...
├── Widgets/                  # Widget tests
│   ├── WidgetTestSuite.php
│   ├── UltimateStatsWidgetTest.php
│   ├── ComprehensiveAnalyticsWidgetTest.php
│   └── ...
├── API/                      # API tests
│   ├── CollectionApiTest.php
│   ├── CollectionIntegrationTest.php
│   └── ...
├── Integration/              # Integration tests
│   ├── CollectionIntegrationTest.php
│   ├── UserImpersonationIntegrationTest.php
│   └── ...
├── Unit/                     # Unit tests
│   ├── CollectionFeatureTest.php
│   ├── SliderTranslationTest.php
│   └── ...
├── Controllers/              # Controller tests
│   ├── NotificationStreamControllerTest.php
│   ├── NotificationControllerTest.php
│   └── ...
├── Models/                   # Model tests
│   ├── SystemSettingTest.php
│   ├── StockResourceTest.php
│   └── ...
├── Pages/                    # Page tests
│   ├── UserImpersonationPageTest.php
│   ├── DashboardTest.php
│   └── ...
├── TestRunner.php            # Centralized test runner
├── TestGroups.php            # Test group configuration
├── TestConfig.php            # Test execution configuration
└── README.md                 # This file
```

## Test Groups

### Admin Tests
Tests for Filament admin resources including:
- Resource CRUD operations
- Form validation
- Table functionality
- Navigation properties
- Model relationships

### Widget Tests
Tests for Filament widgets including:
- Widget instantiation
- View rendering
- Data retrieval
- Chart functionality
- Statistics display

### API Tests
Tests for API endpoints including:
- REST API functionality
- JSON responses
- Authentication
- Rate limiting
- Error handling

### Integration Tests
Tests for system integration including:
- End-to-end workflows
- Cross-component functionality
- Database transactions
- External service integration

### Unit Tests
Tests for individual components including:
- Model functionality
- Service classes
- Helper functions
- Utility methods

### Controller Tests
Tests for HTTP controllers including:
- Request handling
- Response generation
- Middleware functionality
- Route binding

### Model Tests
Tests for Eloquent models including:
- Relationships
- Scopes
- Accessors/Mutators
- Validation rules

### Page Tests
Tests for Livewire pages including:
- Component rendering
- User interactions
- State management
- Event handling

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Group
```bash
php artisan test --filter="Tests\\Feature\\Admin"
php artisan test --filter="Tests\\Feature\\Widgets"
php artisan test --filter="Tests\\Feature\\API"
```

### Run Individual Test
```bash
php artisan test tests/Feature/Admin/ProductResourceTest.php
php artisan test tests/Feature/Widgets/UltimateStatsWidgetTest.php
```

### Using Test Runner Script
```bash
# Run all tests
php run-tests.php all

# Run specific group
php run-tests.php admin
php run-tests.php widgets
php run-tests.php api

# List available groups
php run-tests.php list

# Show help
php run-tests.php help
```

## Test Configuration

### Execution Settings
- Stop on failure: Enabled
- Parallel execution: Disabled
- Coverage: Disabled
- Verbose output: Enabled
- Memory limit: 1G
- Timeout: 300 seconds

### Database Settings
- Connection: SQLite
- Database: In-memory
- Migrations: Enabled
- Seeding: Disabled

### Group Priorities
1. Admin (Priority 1, Timeout 600s, Memory 2G)
2. Widgets (Priority 2, Timeout 300s, Memory 1G)
3. API (Priority 3, Timeout 400s, Memory 1G)
4. Integration (Priority 4, Timeout 800s, Memory 2G)
5. Unit (Priority 5, Timeout 200s, Memory 512M)
6. Controllers (Priority 6, Timeout 300s, Memory 1G)
7. Models (Priority 7, Timeout 400s, Memory 1G)
8. Pages (Priority 8, Timeout 300s, Memory 1G)

## Best Practices

### Test Organization
- Group related tests together
- Use descriptive test names
- Follow naming conventions
- Keep tests focused and atomic

### Test Structure
- Use `RefreshDatabase` trait for database tests
- Mock external dependencies
- Use factories for test data
- Assert specific behaviors

### Performance
- Use in-memory database for speed
- Mock heavy operations
- Limit test data size
- Use appropriate timeouts

### Maintenance
- Update tests when code changes
- Remove obsolete tests
- Keep test data minimal
- Use meaningful assertions

## Troubleshooting

### Common Issues
1. **Memory errors**: Increase memory limit for specific groups
2. **Timeout errors**: Increase timeout for slow tests
3. **Database errors**: Ensure migrations are up to date
4. **Import errors**: Check namespace declarations

### Debugging
- Use `--verbose` flag for detailed output
- Check test logs for specific errors
- Run individual tests to isolate issues
- Use `dd()` or `dump()` for debugging

### Performance Issues
- Run tests in parallel where possible
- Use database transactions for speed
- Mock external services
- Optimize test data generation
