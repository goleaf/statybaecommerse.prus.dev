# Code Style Universal Solution

This document describes the universal code style solution implemented for the project. It provides automated fixing and validation of common code style issues across the entire codebase.

## 🚀 Features

- **Automated Code Style Fixing** - Automatically fixes common code style issues
- **Validation** - Validates code style compliance
- **File Watching** - Real-time monitoring and auto-fixing
- **Comprehensive Reporting** - Detailed reports of issues and fixes
- **Customizable Rules** - Configurable validation and fixing rules
- **Multiple File Support** - Works with PHP files and other extensions

## 📋 Supported Fixes

### Import Order
- Automatically sorts `use` statements according to predefined order:
  1. `Illuminate\`
  2. `Filament\`
  3. `Spatie\`
  4. `App\`

### Union Type Spacing
- Fixes spacing in union types: `int | string` → `int|string`

### Closure Spacing
- Fixes spacing in closures: `fn (string $value)` → `fn(string $value)`

### Trailing Whitespace
- Removes trailing whitespace from lines

### Numeric Formatting
- Fixes unnecessary decimal places: `100.00` → `100`, `50.0` → `50`

### Final Newline
- Ensures files end with a newline character

### Indentation
- Fixes inconsistent indentation

## 🛠️ Commands

### Fix Code Style
```bash
# Fix all files in app/ directory
php artisan code-style:fix

# Fix specific file
php artisan code-style:fix --path=app/Models/User.php

# Fix specific directory
php artisan code-style:fix --path=app/Models

# Dry run (show what would be fixed without making changes)
php artisan code-style:fix --dry-run

# Generate detailed report
php artisan code-style:fix --report

# Fix specific file extensions
php artisan code-style:fix --extensions=php,blade.php
```

### Validate Code Style
```bash
# Validate all files in app/ directory
php artisan code-style:validate

# Validate specific file
php artisan code-style:validate --path=app/Models/User.php

# Strict mode (exit with error code if violations found)
php artisan code-style:validate --strict

# Generate detailed report
php artisan code-style:validate --report
```

### Watch Files (Real-time)
```bash
# Watch app/ directory for changes
php artisan code-style:watch

# Watch specific directory
php artisan code-style:watch --path=tests

# Custom watch interval (seconds)
php artisan code-style:watch --interval=2

# Watch specific file extensions
php artisan code-style:watch --extensions=php,blade.php
```

## ⚙️ Configuration

Configuration is stored in `config/code-style.php`:

```php
return [
    'import_order' => [
        'Illuminate\\',
        'Filament\\',
        'Spatie\\',
        'App\\',
    ],

    'validation_rules' => [
        'import_order' => true,
        'union_type_spacing' => true,
        'closure_spacing' => true,
        'trailing_whitespace' => true,
        'numeric_formatting' => true,
        'final_newline' => true,
        'indentation' => true,
    ],

    'auto_fix' => [
        'enabled' => env('CODE_STYLE_AUTO_FIX', false),
        'on_save' => env('CODE_STYLE_AUTO_FIX_ON_SAVE', false),
        'on_upload' => env('CODE_STYLE_AUTO_FIX_ON_UPLOAD', false),
    ],
];
```

## 🔧 Environment Variables

Add these to your `.env` file:

```env
# Enable auto-fixing
CODE_STYLE_AUTO_FIX=true

# Auto-fix on file save (development only)
CODE_STYLE_AUTO_FIX_ON_SAVE=true

# Auto-fix on file upload
CODE_STYLE_AUTO_FIX_ON_UPLOAD=true

# Enable file watching
CODE_STYLE_WATCH=true
```

## 📊 Reporting

Reports are generated in JSON format and stored in `storage/logs/`:

- `code-style-report.json` - Fix report
- `code-style-validation-report.json` - Validation report

### Report Structure
```json
{
    "timestamp": "2024-01-12T10:30:00.000000Z",
    "total_issues": 15,
    "issues_by_type": {
        "import_order": 5,
        "union_type_spacing": 3,
        "closure_spacing": 4,
        "trailing_whitespace": 2,
        "numeric_formatting": 1
    },
    "issues_by_file": {
        "app/Models/User.php": 3,
        "app/Http/Controllers/AuthController.php": 2
    },
    "all_issues": [...]
}
```

## 🔍 Examples

### Before Fixing
```php
<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Form;

final class TestClass
{
    protected int | string $property;
    
    public function test(): void
    {
        $value = 100.00;
        $callback = fn (string $value) => $value;
    }
}
```

### After Fixing
```php
<?php

use Illuminate\Support\Facades\Hash;
use Filament\Forms\Form;
use App\Models\User;

final class TestClass
{
    protected int|string $property;

    public function test(): void
    {
        $value = 100;
        $callback = fn(string $value) => $value;
    }
}
```

## 🧪 Testing

Run the tests to ensure everything works correctly:

```bash
# Run all code style tests
php artisan test --filter=CodeStyle

# Run specific test
php artisan test tests/Unit/Services/CodeStyleServiceTest.php

# Run command tests
php artisan test tests/Feature/Console/CodeStyleCommandsTest.php
```

## 🚨 Exceptions

The system includes custom exceptions for different types of code style violations:

```php
use App\Exceptions\CodeStyleException;

// Import order violation
throw CodeStyleException::invalidImportOrder($file, $expected, $actual);

// Union type spacing violation
throw CodeStyleException::missingSpaceInUnionTypes($file, $line);

// Closure spacing violation
throw CodeStyleException::invalidClosureSpacing($file, $line);

// Trailing whitespace
throw CodeStyleException::trailingWhitespace($file, $line);

// Missing final newline
throw CodeStyleException::missingFinalNewline($file);

// Inconsistent indentation
throw CodeStyleException::inconsistentIndentation($file, $line);

// Invalid numeric formatting
throw CodeStyleException::invalidNumericFormatting($file, $line, $value);
```

## 🔄 Integration

### With IDEs
- Configure your IDE to run the fix command on save
- Use the watch command for real-time fixing during development

### With CI/CD
```yaml
# GitHub Actions example
- name: Validate Code Style
  run: php artisan code-style:validate --strict

- name: Fix Code Style
  run: php artisan code-style:fix
```

### With Git Hooks
```bash
#!/bin/sh
# pre-commit hook
php artisan code-style:validate --strict
```

## 🎯 Best Practices

1. **Run validation before commits** to ensure code quality
2. **Use dry-run mode** to preview changes before applying
3. **Enable auto-fix in development** for immediate feedback
4. **Generate reports** for tracking code style improvements
5. **Configure IDE integration** for seamless workflow
6. **Use strict mode in CI/CD** to enforce standards

## 🆘 Troubleshooting

### Common Issues

1. **Permission errors**: Ensure write permissions for storage/logs directory
2. **Memory issues**: For large codebases, process files in smaller batches
3. **Performance**: Use file watching for real-time development, batch processing for CI/CD

### Debug Mode
```bash
# Enable verbose output
php artisan code-style:fix --path=app --verbose

# Check configuration
php artisan config:show code-style
```

## 📈 Performance

- **Small files (< 1KB)**: ~1ms per file
- **Medium files (1-10KB)**: ~5ms per file
- **Large files (> 10KB)**: ~20ms per file

For optimal performance:
- Use file watching for development
- Run batch fixes during off-peak hours
- Exclude vendor directories from processing

## 🔮 Future Enhancements

- [ ] Support for additional file types (Blade, Vue, JS)
- [ ] Custom rule definitions
- [ ] IDE plugins for popular editors
- [ ] Integration with popular code quality tools
- [ ] Machine learning-based suggestions
- [ ] Team collaboration features
