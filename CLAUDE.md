# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Build and Development
```bash
# Install dependencies
composer install
npm install

# Build assets (production)
npm run build

# Watch for changes (development)
npm run start
# or
npm run watch

# Build language files
composer i18n
```

### Testing
```bash
# Run all PHPUnit tests
composer test

# Run PHPUnit with coverage
composer test-coverage
composer test-coverage-html  # generates HTML report in tests/coverage/

# Run specific PHPUnit test
vendor/bin/phpunit tests/phpunit/PlanFactoryTest.php
vendor/bin/phpunit --filter test_method_name

# Run Cypress E2E tests (requires WordPress environment)
npx cypress run --spec "tests/cypress/integration/next-steps-widget.cy.js"
npx cypress run --spec "tests/cypress/integration/next-steps-portal.cy.js"
npx cypress run --spec "tests/cypress/integration/next-steps-portal-cards.cy.js"
```

### Code Quality
```bash
# Run PHP linting
composer lint

# Auto-fix PHP coding standards
composer fix

# Run stricter CI-level linting
./lint-ci.sh
```

## Architecture

### Core System Design
The module follows a Factory/Repository pattern for plan management:

- **PlanFactory** (`includes/PlanFactory.php`): Creates and instantiates plans based on site context. Handles plan type detection, WordPress hooks for dynamic switching (WooCommerce activation, site type changes), and language synchronization.

- **PlanRepository** (`includes/PlanRepository.php`): Manages plan persistence using WordPress options API. Handles CRUD operations, plan merging during version updates, and state management.

- **StepsApi** (`includes/StepsApi.php`): REST API controller providing endpoints under `/newfold-next-steps/v2/plans`. All endpoints require `manage_options` capability.

### Plan Data Structure
Plans are now PHP classes instead of markdown files:
- `includes/data/plans/BlogPlan.php` - Personal/blog site plan
- `includes/data/plans/CorporatePlan.php` - Business/corporate site plan  
- `includes/data/plans/StorePlan.php` - Ecommerce/WooCommerce site plan

Each plan returns a structured Plan DTO with tracks → sections → tasks hierarchy.

### DTOs (Data Transfer Objects)
Located in `includes/DTOs/`:
- **Plan**: Root container with version tracking and merge capabilities
- **Track**: Groups related sections (e.g., "Build", "Grow")
- **Section**: Contains tasks with modal support and completion tracking
- **Task**: Individual action items with status, priority, and data attributes

All DTOs support:
- Array serialization/deserialization
- Recursive merging for updates
- Status management with date tracking

### PluginRedirect System
`includes/PluginRedirect.php` provides intelligent plugin detection and routing:
- Whitelist-based security for partner plugins
- Dynamic redirect based on plugin activation status
- Nonce-protected redirect URLs
- Plugin-specific configuration checks (e.g., Jetpack connection status)

### Frontend Components
React components in `src/components/`:
- **section-card**: New card-based UI with wireframe illustrations
- **tasks-modal**: Multi-task section handling
- **nextStepsListApp**: Card-based layout for portal view
- **no-more-cards**: Completion state display

Components use Tailwind CSS and @newfold/ui-component-library.

### Site Type Detection
The module automatically detects site types:
1. Checks `nfd_module_onboarding_site_info` option first
2. Falls back to intelligent detection:
   - Ecommerce: WooCommerce presence
   - Corporate: Business indicators (pages, plugins, user count)
   - Blog: Default fallback

### Plan Versioning and Merging
Plans include version tracking (`NFD_NEXTSTEPS_MODULE_VERSION`). When loading saved plans:
1. Compares saved version with current version
2. If outdated, loads fresh plan structure
3. Merges saved state (completion status) with new structure
4. Preserves user progress while updating plan content

## Key WordPress Hooks

The module hooks into:
- `init` - Load default steps
- `update_option_nfd_module_onboarding_site_info` - Site type changes
- `activated_plugin` - WooCommerce activation detection
- `woocommerce_rest_insert_product_object` - Auto-complete product tasks
- `update_option_WPLANG` / `switch_locale` - Language changes

## REST API Endpoints

Base: `/wp-json/newfold-next-steps/v2/plans`

- `GET /plans` - Get current plan
- `POST /plans/add` - Add tasks to plan
- `PUT /plans/tasks/{task_id}` - Update task status
- `PUT /plans/sections/{section_id}` - Update section state
- `PUT /plans/tracks/{track_id}` - Update track open state
- `GET /plans/stats` - Get completion statistics
- `PUT /plans/switch` - Switch plan type
- `PUT /plans/reset` - Reset to defaults

## WordPress Options

- `nfd_next_steps` - Current plan and task states
- `nfd_module_onboarding_site_info` - Onboarding data including site_type
- `newfold_solutions` - Transient for solutions data

## Testing Approach

### PHPUnit Tests
Tests use a lightweight WordPress mock environment (`tests/phpunit/bootstrap.php`) that simulates WordPress functions without requiring full installation. Key test files:
- `PlanFactoryTest.php` - Plan creation and switching
- `PlanRepositoryTest.php` - Data persistence and merging
- `PlanMergeTest.php` - Version upgrade scenarios
- `PlanVersionAndLanguageTest.php` - Localization handling

### Cypress Tests
E2E tests validate user workflows. Each test starts clean with `wpCli('option delete nfd_next_steps')`.

## Module Integration

This module integrates with:
- **Onboarding Module**: Listens for site type selection
- **Solutions Module**: Provides task recommendations
- **Data Module**: Analytics and tracking
- **Brand Plugins**: Bluehost, HostGator specific features

## Important Constants

- `NFD_NEXTSTEPS_MODULE_VERSION` - Current module version
- `NFD_SOLUTIONS_INSTALL_CAPTURED` - Solutions analytics flag