# Architecture

## Core System Design

The module follows a Factory/Repository pattern for plan management with smart task completion:

- **PlanFactory** (`includes/PlanFactory.php`): Creates and instantiates plans based on site context. Handles plan type detection and language synchronization. Initializes default plans on first load.

- **PlanRepository** (`includes/PlanRepository.php`): Manages plan persistence using WordPress options API. Handles CRUD operations, plan merging during version updates, state management, and triggers validation for new plans via `TaskStateValidator`.

- **PlanSwitchTriggers** (`includes/PlanSwitchTriggers.php`): Handles dynamic plan switching based on site changes (WooCommerce activation, site type changes, language updates). Separated from PlanFactory for single responsibility.

- **TaskCompletionTriggers** (`includes/TaskCompletionTriggers.php`): Smart task completion system that automatically marks tasks complete based on user actions. Registers WordPress hooks for various events (product creation, plugin activation, post publishing, etc.) and validates existing site state on plan initialization.

- **TaskStateValidator** (`includes/TaskStateValidator.php`): Registry pattern for validating existing site conditions. Automatically checks if tasks should be marked complete when a new plan is loaded or switched, ensuring existing sites get proper task completion status.

- **StepsApi** (`includes/StepsApi.php`): REST API controller providing endpoints under `/newfold-next-steps/v2/plans`. All endpoints require `manage_options` capability.

## Plan Data Structure

Plans are PHP classes instead of markdown files:

- `includes/data/plans/BlogPlan.php` – Personal/blog site plan
- `includes/data/plans/CorporatePlan.php` – Business/corporate site plan
- `includes/data/plans/StorePlan.php` – Ecommerce/WooCommerce site plan

Each plan returns a structured Plan DTO with tracks → sections → tasks hierarchy.

## DTOs (Data Transfer Objects)

Located in `includes/DTOs/`:

- **Plan**: Root container with version tracking, merge capabilities, and helper methods (`has_track()`, `has_section()`, `has_task()`, `has_exact_task()`) for efficient existence checks
- **Track**: Groups related sections (e.g., "Build", "Grow")
- **Section**: Contains tasks with modal support, completion tracking, and automatic task status propagation (marking section as done/dismissed marks all tasks accordingly)
- **Task**: Individual action items with status, priority, and data attributes

All DTOs support: array serialization/deserialization, recursive merging for updates, status management with date tracking, helper methods for status checks (`is_completed()`, `is_dismissed()`).

## PluginRedirect System

`includes/PluginRedirect.php` provides intelligent plugin detection and routing: whitelist-based security for partner plugins, dynamic redirect based on plugin activation status, nonce-protected redirect URLs, plugin-specific configuration checks (e.g., Jetpack connection status).

## Frontend Components

React components in `src/components/`: section-card, tasks-modal, nextStepsListApp, no-more-cards. Components use Tailwind CSS and @newfold/ui-component-library.

## Site Type Detection

The module automatically detects site types:

1. Checks `nfd_module_onboarding_site_info` option first
2. Falls back to intelligent detection: Ecommerce (WooCommerce presence), Corporate (business indicators), Blog (default fallback)

## Plan Versioning and Merging

Plans include version tracking (`NFD_NEXTSTEPS_MODULE_VERSION`). When loading saved plans: compares saved version with current version; if outdated, loads fresh plan structure; merges saved state (completion status) with new structure; preserves user progress while updating plan content.

## Smart Task Completion System

### Architecture

1. **Task Path Constants**: All task paths are defined in `TaskCompletionTriggers::TASK_PATHS` (format: `plan_id.track_id.section_id.task_id`).
2. **Registration Methods**: Each task type has a registration method that sets up WordPress action hooks and state validators.
3. **Handler Methods**: Public static methods that respond to WordPress events and mark tasks complete.
4. **Validator Methods**: Public static methods that check existing site state during plan initialization.

### Supported Task Types

WooCommerce (product creation, payment gateway setup); Content (blog post, gift cards, welcome popups); Customization (logo upload); Plugin activation (Jetpack, Yoast SEO Premium, etc.). See `TaskCompletionTriggers` for the full list.

### Key WordPress Hooks

Plan management: `init`, `update_option_nfd_module_onboarding_site_info`, `activated_plugin`, `update_option_WPLANG` / `switch_locale`. Task completion: `woocommerce_rest_insert_product_object`, `publish_product`, `publish_post`, `update_option_woocommerce_{gateway_id}_settings`, `customize_save_after`, `update_option_site_logo`, `jetpack_site_registered`, `jetpack_activate_module`.

### Adding New Smart Tasks

1. Add task path to `TASK_PATHS` constant
2. Create registration method: `register_{task_type}_hooks_and_validators()`
3. Call registration method in constructor
4. Create handler method: `on_{event_name}()` (public static)
5. Create validator method: `validate_{task_type}_state()` (public static)
6. Update class documentation

## WordPress Options

- `nfd_next_steps` – Current plan and task states
- `nfd_module_onboarding_site_info` – Onboarding data including site_type
- `newfold_solutions` – Transient for solutions data

## Testing Approach

PHPUnit/WPUnit via Codeception; `.env.testing` and `codeception.yml`; wp-env for local WordPress. Key test files: PlanFactoryWPUnitTest, PlanRepositoryWPUnitTest, PlanHelpersWPUnitTest, SectionDismissWPUnitTest, TaskStateValidatorWPUnitTest. Playwright E2E: `npx playwright test`.

## Important Constants

- `NFD_NEXTSTEPS_MODULE_VERSION` – Current module version
- `NFD_SOLUTIONS_INSTALL_CAPTURED` – Solutions analytics flag
- `PlanFactory::ONBOARDING_SITE_INFO_OPTION` – Option name for onboarding data
- `PlanFactory::PLAN_TYPES` – Map of site types to internal plan types
- `TaskCompletionTriggers::TASK_PATHS` – Centralized task path definitions

## Coding Patterns and Best Practices

Task completion handlers: use `public static` for handlers and validators; defensive checks for external APIs; use `mark_task_as_complete_by_path()` with `TASK_PATHS`. DTO usage: use `has_exact_task()`, `is_completed()`, `is_dismissed()`; section status propagates to tasks. Plan data files: align array arrows for linting; all tasks require `data_attributes`; use descriptive unique task IDs.
