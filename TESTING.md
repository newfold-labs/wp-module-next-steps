# Testing Setup

This module uses both PHPUnit and Codeception for comprehensive testing, following the pattern established in other Newfold Labs modules.

## Test Suites

### PHPUnit Tests
- **Location**: `tests/phpunit/`
- **Purpose**: Unit tests for individual classes and methods
- **Count**: 57 tests, 241 assertions
- **Run**: `composer test` or `vendor/bin/phpunit --bootstrap tests/phpunit/bootstrap.php`

### Codeception Tests
- **Location**: `tests/unit/` and `tests/wpunit/`
- **Purpose**: Integration tests and WordPress-specific tests
- **Count**: 3 wpunit tests, 3 assertions
- **Run**: `vendor/bin/codecept run unit` or `vendor/bin/codecept run wpunit`

## Environment Setup

### Local Development

1. **Create Environment File**:
   ```bash
   cp .env.testing.example .env.testing
   ```

2. **Start WordPress Test Environment**:
   ```bash
   # Start wp-env (provides MySQL database and WordPress)
   npx @wordpress/env start
   ```

3. **Configure Environment Variables**:
   The `.env.testing` file should contain:
   ```bash
   # WordPress root folder (installed by wp-env)
   WP_ROOT_FOLDER="wordpress"
   
   # Database settings (matches wp-env configuration)
   TEST_DB_HOST="127.0.0.1"
   TEST_DB_PORT="33306"
   TEST_DB_USER="root"
   TEST_DB_PASSWORD="password"
   TEST_DB_NAME="wp-browser-tests"
   TEST_TABLE_PREFIX="wp_"
   
   # WordPress site settings
   TEST_SITE_WP_DOMAIN="localhost:8888"
   TEST_SITE_ADMIN_EMAIL="admin@example.com"
   ```

4. **Install Coverage Driver** (optional):
   ```bash
   # macOS with Homebrew
   brew install pcov
   
   # Ubuntu/Debian
   sudo apt-get install php-pcov
   
   # Verify installation
   php -m | grep pcov
   ```

### Running Tests

```bash
# Run all tests (PHPUnit + Codeception)
composer run test

# Run only PHPUnit tests (57 tests, 241 assertions)
vendor/bin/phpunit --bootstrap tests/phpunit/bootstrap.php

# Run only Codeception unit tests (no WordPress required)
vendor/bin/codecept run unit

# Run WordPress integration tests (3 tests, 3 assertions)
vendor/bin/codecept run wpunit

# Generate coverage report
composer run test-coverage

# Run tests with coverage (requires pcov/xdebug)
vendor/bin/codecept run wpunit --coverage-html tests/_output/coverage
```

## GitHub Actions

The `codecoverage-main.yml` workflow automatically:
- Sets up PHP 7.3-8.4 (comprehensive version testing)
- Provides MySQL database service (port 33306)
- Runs PHPUnit and Codeception tests
- Generates and merges coverage reports
- Publishes coverage reports to GitHub Pages
- Creates coverage badges and PR comments
- Analyzes patch coverage for PRs
- Uses pinned dependency versions for security
- Includes proper permissions for workflow actions

## Coverage Configuration

Coverage includes:
- ✅ `includes/*` - Main module code
- ❌ `includes/Data/Plans/*` - Excludes plan data files
- ❌ `tests/*`, `vendor/*`, `build/*`, `src/*`, `languages/*` - Also excluded

## Troubleshooting

### WPLoader Configuration Issues
If you see "WPLoader module is not configured" errors:
1. Ensure `.env.testing` exists with proper values
2. Check that database credentials are correct
3. Verify WordPress test environment is set up with `npx @wordpress/env start`
4. Ensure all required environment variables are present (especially `TEST_SITE_ADMIN_EMAIL`)

### Database Connection Issues
If you see "Connection refused" errors:
1. Start wp-env: `npx @wordpress/env start`
2. Verify MySQL is running on port 33306
3. Check that database credentials match wp-env configuration
4. Ensure `.env.testing` has correct database settings

### Environment Variable Parsing Issues
If you see "Failed to parse dotenv file" errors:
1. Check for malformed lines in `.env.testing`
2. Ensure no duplicate variable names
3. Verify proper quoting of values
4. Copy from `.env.testing.example` if needed

### Coverage Driver Issues
If you see "No code coverage driver available":
1. Install pcov or xdebug extension
2. Verify extension is loaded: `php -m | grep pcov`
3. Restart your web server/CLI environment

### Test Failures
If tests are failing:
1. Run `composer run test` to see full output
2. Check that all dependencies are installed: `composer install`
3. Verify WordPress is properly set up in the `wordpress/` directory
4. Ensure cache is cleared: `PlanRepository::invalidate_cache()` in tests
