<a href="https://newfold.com/" target="_blank">
    <img src="https://newfold.com/content/experience-fragments/newfold/site-header/master/_jcr_content/root/header/logo.coreimg.svg/1621395071423/newfold-digital.svg" alt="Newfold Logo" title="Newfold Digital" align="right" height="42" />
</a>

# WordPress Next Steps Module
[![Version Number](https://img.shields.io/github/v/release/newfold-labs/wp-module-next-steps?color=21a0ed&labelColor=333333)](https://github.com/newfold-labs/wp-module-next-steps/releases)
[![Lint](https://github.com/newfold-labs/wp-module-next-steps/actions/workflows/lint-php.yml/badge.svg)](https://github.com/newfold-labs/wp-module-next-steps/actions/workflows/lint-php.yml)
[![Build](https://github.com/newfold-labs/wp-module-next-steps/actions/workflows/wp-module-build.yml/badge.svg)](https://github.com/newfold-labs/wp-module-next-steps/actions/workflows/wp-module-build.yml)
[![License](https://img.shields.io/github/license/newfold-labs/wp-module-next-steps?labelColor=333333&color=666666)](https://raw.githubusercontent.com/newfold-labs/wp-module-next-steps/master/LICENSE)

A WordPress module for managing personalized next steps for users after completing the onboarding flow. This module provides contextual tasks and guidance based on the user's site type (personal blog, business, or ecommerce).

## Module Responsibilities

- Provides personalized next steps based on site type (personal blog, business/corporate, ecommerce)
- Integrates with onboarding module to automatically load appropriate task sets when `nfd_module_onboarding_site_info` is updated
- Intelligently detects site type for existing installations using plugin analysis, content detection, and site characteristics
- Displays next steps via dashboard widget and dedicated portal interface  
- Manages task completion state and progress tracking across multiple tracks and sections
- Exposes hooks and API for other modules to integrate with next steps functionality
- Provides plan switching capabilities when site type changes post-onboarding
- Maintains task persistence through WordPress options API for reliable state management
- Supports three distinct plan types with contextually relevant tasks:
  - **Personal/Blog**: Content creation, SEO basics, social sharing
  - **Business/Corporate**: Professional features, team collaboration, lead generation
  - **Ecommerce**: Store setup, payment processing, product management, shipping configuration

## Critical Paths

- When a user completes onboarding and selects a site type, the appropriate next steps plan should be automatically loaded and available in the dashboard widget
- For existing sites without onboarding data, the module should detect site characteristics and load the most appropriate plan without user intervention
- Task completion and progress should persist across page loads and remain accurate when users return to the dashboard
- Plan switching should work seamlessly when site type changes (e.g., user adds WooCommerce to convert from blog to ecommerce)
- The dashboard widget should display current progress and provide clear calls-to-action for incomplete tasks

## Features

- **Smart Site Detection**: Automatically detects site type for existing installations based on plugins, content, and configuration
- **Dynamic Plan Loading**: Loads appropriate next steps based on onboarding choices or detected site characteristics
- **Multiple Site Types**: Supports personal/blog, business/corporate, and ecommerce site types
- **Task Management**: Track completion status of individual tasks and sections
- **Dashboard Widget**: Integrated WordPress dashboard widget
- **Standalone Portal**: Full-page next steps interface
- **Responsive Design**: Works across all device sizes

## Installation

### 1. Add the Newfold Satis to your `composer.json`.

```bash
composer config repositories.newfold composer https://newfold-labs.github.io/satis
```

### 2. Require the `newfold-labs/wp-module-next-steps` package.

```bash
composer require newfold-labs/wp-module-next-steps
```

## Usage

### Basic Integration

```php
// Include in your plugin's bootstrap
use NewfoldLabs\WP\Module\NextSteps\NextSteps;

// Initialize the module
$next_steps = new NextSteps();
```

### Site Type Integration

The module integrates with onboarding flows by listening for site type changes:

```php
// The module automatically hooks into this option
update_option('nfd_module_onboarding_site_info', [
    'site_type' => 'personal', // 'personal', 'business', or 'ecommerce'
    // ... other onboarding data
]);
```

## Architecture

### Core Classes

- **`PlanManager`**: Manages plan loading, switching, and task updates
- **`PlanLoader`**: Handles initial setup and dynamic plan switching
- **`StepsApi`**: WordPress option-based data persistence
- **DTOs**: Strongly-typed data objects (`Plan`, `Track`, `Section`, `Task`)

### Site Type Detection

For existing sites without onboarding data, the module includes intelligent detection:

- **Ecommerce**: Detects WooCommerce, product post types, other ecommerce plugins
- **Business**: Analyzes user count, business pages, professional plugins, site naming
- **Personal**: Default fallback for simple sites

## Testing

### Prerequisites

- PHP 7.3+
- Composer
- WordPress test environment (optional, for full integration tests)

### PHPUnit Tests

#### Setup

Install development dependencies:

```bash
composer install
```

#### Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage report
composer test-coverage

# Generate HTML coverage report
composer test-coverage-html
```

#### Test Structure

The test suite includes:

- **Unit Tests**: `tests/phpunit/`
  - `PlanManagerTest.php` - Tests plan loading, switching, and task management (21 tests)
  - `PlanLoaderTest.php` - Tests site detection and dynamic loading (19 tests)

#### Test Environment

Tests run in a lightweight WordPress environment with:

- Mock WordPress functions (`get_option`, `update_option`, etc.)
- Isolated test state (cleaned between tests)
- Fallback support for environments without full WordPress setup

#### Writing Tests

```php
<?php
use NewfoldLabs\WP\Module\NextSteps\PlanManager;

class MyTest extends WP_UnitTestCase {
    
    public function setUp(): void {
        parent::setUp();
        // Clean options before each test
        delete_option(PlanManager::OPTION);
        delete_option(PlanManager::SOLUTION_OPTION);
    }
    
    public function test_my_feature() {
        // Your test code here
        $result = PlanManager::get_current_plan();
        $this->assertNotNull($result);
    }
}
```

### Cypress End-to-End Tests

#### Prerequisites

- WordPress test environment running
- Cypress installed in parent project

#### Running E2E Tests

```bash
# From the plugin root directory
npx cypress run --spec "vendor/newfold-labs/wp-module-next-steps/tests/cypress/integration/next-steps-widget.cy.js"
npx cypress run --spec "vendor/newfold-labs/wp-module-next-steps/tests/cypress/integration/next-steps-portal.cy.js"
```

#### E2E Test Structure

- **Widget Tests**: Tests dashboard widget functionality, interactions, and state management
- **Portal Tests**: Tests full-page portal interface and navigation

#### Test Features

- **Clean State**: Each test starts with a clean slate using `wpCli('option delete nfd_next_steps')`
- **Combined Test Blocks**: Optimized for speed by reducing login operations
- **Real User Interactions**: Tests actual user workflows and task completion

### Code Quality

#### Linting

```bash
# Run PHP Code Sniffer
composer lint

# Auto-fix coding standards issues
composer fix

# Run CI-matching standards (stricter)
./lint-ci.sh
```

#### Standards

The module follows WordPress coding standards with Newfold-specific extensions.

### Continuous Integration

Tests run automatically on:

- Pull requests
- Pushes to main branches
- Manual triggers

CI includes:
- PHPUnit tests
- Cypress E2E tests
- Code quality checks
- WordPress compatibility testing

## Releases

Run the `Newfold Prepare Release` github action to automatically bump the version (either patch, minor or major version), and update build and language files all at once. It will create a PR with changed files for review.

### Manual release steps

1. This module has a constant `NEXT_STEPS_VERSION` which needs to be incremented in conjunction with new releases and updates via github tagging.
2. Update the version in the `composer.json` file.
3. Update build files and/or language files to reflect new version.
4. Create release branch and release PR.
5. After merge, create a new release with proper semantic versioning.

## API Reference

### PlanManager

```php
// Get current active plan
$plan = PlanManager::get_current_plan();

// Switch to different plan type
$plan = PlanManager::switch_plan('ecommerce');

// Update task status
PlanManager::update_task_status('track_id', 'section_id', 'task_id', 'done');

// Get plan statistics
$stats = PlanManager::get_plan_stats();
```

### PlanLoader

```php
// Manually trigger site type detection
$detected_type = PlanLoader::detect_site_type();

// Load default steps (usually automatic)
PlanLoader::load_default_steps();
```

## Configuration

### Site Type Mapping

The module maps onboarding site types to internal plan types:

```php
const PLAN_TYPES = [
    'personal'  => 'blog',      // Personal sites → Blog plan
    'business'  => 'corporate', // Business sites → Corporate plan  
    'ecommerce' => 'ecommerce', // Ecommerce sites → Ecommerce plan
];
```

### WordPress Options

- `nfd_next_steps`: Stores current plan and task states
- `nfd_next_steps_solution`: Stores current solution type
- `nfd_module_onboarding_site_info`: Integration point for onboarding data

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for new functionality
4. Ensure all tests pass: `composer test`
5. Follow coding standards: `composer lint`
6. Submit a pull request

### Development Workflow

```bash
# Install dependencies
composer install

# Run tests during development
composer test

# Check code quality
composer lint

# Auto-fix simple issues
composer fix

# Run full CI simulation
./lint-ci.sh && composer test
```

## License

GPL-3.0-or-later

## Support

For issues and feature requests, please use the GitHub issue tracker. 