# Testing Setup

This module uses both PHPUnit and Codeception for testing, following the pattern established in other modules.

## Test Suites

### PHPUnit Tests
- **Location**: `tests/phpunit/`
- **Purpose**: Unit tests for individual classes and methods
- **Run**: `composer test` or `vendor/bin/phpunit --bootstrap tests/phpunit/bootstrap.php`

### Codeception Tests
- **Location**: `tests/unit/` and `tests/wpunit/`
- **Purpose**: Integration tests and WordPress-specific tests
- **Run**: `vendor/bin/codecept run unit` or `vendor/bin/codecept run wpunit`

## Environment Setup

### Local Development

1. **Create Environment File**:
   ```bash
   cp env.testing.example .env.testing
   ```

2. **Configure Database**:
   Edit `.env.testing` with your local database settings:
   ```bash
   WP_ROOT_FOLDER=/wordpress
   TEST_DB_HOST=localhost
   TEST_DB_PORT=3306
   TEST_DB_USER=root
   TEST_DB_PASSWORD=your_password
   TEST_DB_NAME=wp_test
   TEST_SITE_WP_DOMAIN=localhost
   TEST_SITE_ADMIN_EMAIL=admin@example.com
   TEST_TABLE_PREFIX=wp_
   ```

3. **Install Coverage Driver** (optional):
   ```bash
   # macOS with Homebrew
   brew install pcov
   
   # Ubuntu/Debian
   sudo apt-get install php-pcov
   ```

### Running Tests

```bash
# Run all tests
composer test

# Run only PHPUnit tests
vendor/bin/phpunit --bootstrap tests/phpunit/bootstrap.php

# Run only Codeception unit tests (no WordPress required)
vendor/bin/codecept run unit

# Run WordPress integration tests (requires WordPress setup)
vendor/bin/codecept run wpunit

# Generate coverage report
vendor/bin/codecept run unit --coverage-html tests/_output/coverage
```

## GitHub Actions

The existing `codecoverage-main.yml` workflow automatically:
- Sets up PHP 7.3-8.4 (comprehensive version testing)
- Provides MySQL database service
- Runs PHPUnit and Codeception tests
- Generates and merges coverage reports
- Publishes coverage reports to GitHub Pages
- Creates coverage badges and PR comments
- Analyzes patch coverage for PRs

## Coverage Configuration

Coverage includes:
- ✅ `includes/*` - Main module code
- ❌ `includes/Data/Plans/*` - Plan data files
- ❌ `tests/*`, `vendor/*`, `build/*`, `src/*`, `languages/*` - Excluded

## Troubleshooting

### WPLoader Configuration Issues
If you see "WPLoader module is not configured" errors:
1. Ensure `.env.testing` exists with proper values
2. Check that database credentials are correct
3. Verify WordPress test environment is set up

### Coverage Driver Issues
If you see "No code coverage driver available":
1. Install pcov or xdebug extension
2. Verify extension is loaded: `php -m | grep pcov`
3. Restart your web server/CLI environment
