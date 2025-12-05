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

# Run Playwright E2E tests (requires WordPress environment)
npx playwright test tests/playwright/specs/next-steps-widget.spec.mjs
npx playwright test tests/playwright/specs/next-steps-portal.spec.mjs
npx playwright test tests/playwright/specs/next-steps-portal-cards.spec.mjs
# Or run all tests
npx playwright test
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
The module follows a Factory/Repository pattern for plan management with smart task completion:

- **PlanFactory** (`includes/PlanFactory.php`): Creates and instantiates plans based on site context. Handles plan type detection and language synchronization. Initializes default plans on first load.

- **PlanRepository** (`includes/PlanRepository.php`): Manages plan persistence using WordPress options API. Handles CRUD operations, plan merging during version updates, state management, and triggers validation for new plans via `TaskStateValidator`.

- **PlanSwitchTriggers** (`includes/PlanSwitchTriggers.php`): Handles dynamic plan switching based on site changes (WooCommerce activation, site type changes, language updates). Separated from PlanFactory for single responsibility.

- **TaskCompletionTriggers** (`includes/TaskCompletionTriggers.php`): Smart task completion system that automatically marks tasks complete based on user actions. Registers WordPress hooks for various events (product creation, plugin activation, post publishing, etc.) and validates existing site state on plan initialization.

- **TaskStateValidator** (`includes/TaskStateValidator.php`): Registry pattern for validating existing site conditions. Automatically checks if tasks should be marked complete when a new plan is loaded or switched, ensuring existing sites get proper task completion status.

- **StepsApi** (`includes/StepsApi.php`): REST API controller providing endpoints under `/newfold-next-steps/v2/plans`. All endpoints require `manage_options` capability.

### Plan Data Structure
Plans are now PHP classes instead of markdown files:
- `includes/data/plans/BlogPlan.php` - Personal/blog site plan
- `includes/data/plans/CorporatePlan.php` - Business/corporate site plan  
- `includes/data/plans/StorePlan.php` - Ecommerce/WooCommerce site plan

Each plan returns a structured Plan DTO with tracks → sections → tasks hierarchy.

### DTOs (Data Transfer Objects)
Located in `includes/DTOs/`:
- **Plan**: Root container with version tracking, merge capabilities, and helper methods (`has_track()`, `has_section()`, `has_task()`, `has_exact_task()`) for efficient existence checks
- **Track**: Groups related sections (e.g., "Build", "Grow")
- **Section**: Contains tasks with modal support, completion tracking, and automatic task status propagation (marking section as done/dismissed marks all tasks accordingly)
- **Task**: Individual action items with status, priority, and data attributes

All DTOs support:
- Array serialization/deserialization
- Recursive merging for updates
- Status management with date tracking
- Helper methods for status checks (`is_completed()`, `is_dismissed()`)

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

## Smart Task Completion System

### Architecture
The smart task completion system uses a centralized registry pattern:

1. **Task Path Constants**: All task paths are defined once in `TaskCompletionTriggers::TASK_PATHS` constant using format: `plan_id.track_id.section_id.task_id`

2. **Registration Methods**: Each task type has a registration method that sets up both:
   - WordPress action hooks for real-time completion
   - State validators for existing site conditions

3. **Handler Methods**: Public static methods that respond to WordPress events and mark tasks complete

4. **Validator Methods**: Public static methods that check existing site state during plan initialization

### Supported Task Types

**WooCommerce Tasks:**
- Product creation (via REST API and `publish_product` hook)
- Payment gateway setup (via gateway option updates)

**Content Creation:**
- Blog post publishing (excluding default "Hello World" post)
- Gift cards (`bh_gift_card` post type)
- Welcome popups (`yith_campaign` post type)

**Customization:**
- Logo upload (via Customizer and Site Editor, with block theme support)

**Plugin Activation:**
- Jetpack connection and Boost module
- Jetpack Stats module
- Yoast SEO Premium
- Advanced Reviews plugin
- YITH WooCommerce Affiliates
- Email Templates plugin

### Key WordPress Hooks

**Plan Management:**
- `init` - Load default steps
- `update_option_nfd_module_onboarding_site_info` - Site type changes (PlanSwitchTriggers)
- `activated_plugin` - WooCommerce activation, plugin detection (PlanSwitchTriggers, TaskCompletionTriggers)
- `update_option_WPLANG` / `switch_locale` - Language changes (PlanSwitchTriggers)

**Task Completion:**
- `woocommerce_rest_insert_product_object` - Product creation via REST API
- `publish_product` - Product creation via admin
- `publish_post` - Blog posts, gift cards, welcome popups
- `update_option_woocommerce_{gateway_id}_settings` - Payment gateway setup
- `customize_save_after` - Logo upload via Customizer
- `update_option_site_logo` - Logo upload via Site Editor
- `jetpack_site_registered` - Jetpack connection
- `jetpack_activate_module` - Jetpack module activation

### Adding New Smart Tasks

To add a new smart task:

1. Add task path to `TASK_PATHS` constant
2. Create registration method: `register_{task_type}_hooks_and_validators()`
3. Call registration method in constructor
4. Create handler method: `on_{event_name}()` (public static)
5. Create validator method: `validate_{task_type}_state()` (public static)
6. Update class documentation with new section

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

### PHPUnit/WPUnit Tests
The module uses Codeception with WPUnit tests that run against a real WordPress installation via `wp-env`. Configuration:
- `.env.testing` - Database and WordPress configuration for tests
- `codeception.yml` - Test suite configuration
- Tests require `wp-env` to be running

Key test files:
- `tests/wpunit/PlanFactoryWPUnitTest.php` - Plan creation and switching
- `tests/wpunit/PlanRepositoryWPUnitTest.php` - Data persistence and merging
- `tests/wpunit/PlanHelpersWPUnitTest.php` - Plan DTO helper methods
- `tests/wpunit/SectionDismissWPUnitTest.php` - Section dismissal and task propagation
- `tests/wpunit/TaskStateValidatorWPUnitTest.php` - State validation system

### Running Tests in wp-env
```bash
# Ensure wp-env is running
wp-env start

# Run tests
composer test

# The tests connect to the wp-env database automatically
# Check .env.testing for database configuration
```

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
- `PlanFactory::ONBOARDING_SITE_INFO_OPTION` - Option name for onboarding data
- `PlanFactory::PLAN_TYPES` - Map of site types to internal plan types
- `TaskCompletionTriggers::TASK_PATHS` - Centralized task path definitions

## Coding Patterns and Best Practices

### Task Completion Handlers
- All handler methods must be `public static` (required for WordPress hooks)
- All validator methods must be `public static` (required for TaskStateValidator registry)
- Helper methods should be `private static`
- Use defensive checks before calling external APIs (e.g., `function_exists('WC')`, `class_exists('Jetpack')`)
- Always check plan type before marking tasks complete
- Use `mark_task_as_complete_by_path()` with `TASK_PATHS` constants for consistency

### DTO Usage
- Use helper methods for existence checks: `has_exact_task()` is most efficient when all IDs are known
- Use `is_completed()` and `is_dismissed()` instead of direct status property access
- Section status changes automatically propagate to tasks (done → all tasks done, dismissed → all tasks dismissed)

### Plan Data Files
- Array double arrows must be aligned for linting (use consistent spacing)
- All tasks require `data_attributes` array (even if empty)
- Task IDs should be descriptive and unique within their section

### Testing
- Always run tests after making changes: `composer test`
- Tests must pass before committing
- Use `wp-env` for local WordPress environment
- Check `.env.testing` configuration if tests fail to connect

### Code Quality
- Run `composer fix` to auto-fix coding standards
- Run `composer lint` to check for issues
- Follow WordPress coding standards
- Use meaningful variable names and add PHPDoc comments