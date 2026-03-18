---
name: wp-module-next-steps
title: Testing
description: E2E and unit tests, commands, CI workflows, and PR expectations.
updated: 2025-03-16
---

# Testing

This module has **unit tests** (PHP/Codeception WPUnit) and **E2E tests** (Playwright). All tests must pass before a PR is merged, and new code should include new or updated tests in the same PR.

## Unit tests (PHP / Codeception WPUnit)

- **Location:** `tests/wpunit/` (and support in `tests/_support/`, `tests/_envs/`).
- **Config:** `codeception.yml`, `tests/wpunit.suite.yml`; env vars in `.env.testing` (e.g. `WP_ROOT_FOLDER`, `TEST_DB_*`).
- **Stack:** Codeception with wp-browser (WPLoader). PHP 7.3+.

### Commands

| Command | Description |
|--------|-------------|
| `composer test` | Run WPUnit suite: `codecept run wpunit`. |
| `composer test-coverage` | Run WPUnit with coverage, merge and output HTML report (open `tests/_output/html/index.html`). |

Local runs require a WordPress install (e.g. `wp-env`). See [development.md](development.md) and `.env.testing`.

## E2E tests (Playwright)

- **Location:** `tests/playwright/`.
- **Stack:** Playwright; runs against a brand plugin (e.g. Bluehost) that includes this module.

The module’s Playwright tests reuse **helper methods from the brand plugin** (e.g. Bluehost). The module’s `tests/playwright/helpers/index.mjs` imports the plugin’s helpers (such as `auth`, `wordpress`/`wpCli`, `newfold`, `a11y`, `utils`) from the plugin’s `tests/playwright/helpers/` when `PLUGIN_DIR` is set, and adds Next Steps–specific helpers (e.g. `setTestNextStepsData`, `resetNextStepsData`). Specs use both: plugin helpers for login and WordPress interaction, and module helpers for fixture data.

### Commands

| Command | Description |
|--------|-------------|
| `npx playwright test` | Run Playwright E2E tests. Requires WordPress (e.g. `wp-env start`) and the module loaded in a brand plugin. |

## CI workflows that run tests

| Workflow | Triggers | What it runs |
|----------|----------|----------------|
| **Codecoverage-Main** (`.github/workflows/codecoverage-main.yml`) | Push and pull requests to `main` | PHPUnit/Codeception WPUnit tests via `newfold-labs/workflows` reusable `reusable-codecoverage.yml`; multiple PHP versions; coverage report to GitHub Pages. |
| **Build and Test Module Updates in Brand Plugins (Playwright tests)** (`.github/workflows/brand-plugin-test-playwright.yml`) | Pull requests (opened, reopened, ready_for_review, synchronize) to `main`; also `workflow_dispatch` | Playwright E2E via `module-plugin-test-playwright.yml` against the Bluehost plugin with the module branch. |

Lint runs in **Lint Checker: PHP** (`lint-checker-php.yml`) on PHP changes; tests are separate from lint.

## PR expectations

- **All tests must pass** before a PR is merged. CI will run unit and E2E workflows on PRs to `main`.
- **New code should include new tests** in the same PR where the code is introduced (unit tests for PHP logic, E2E where user flows are added or changed). Update or add tests when changing behavior so the test suite remains the source of truth.

## See also

- [development.md](development.md) — Lint, test, and day-to-day workflow.
- [getting-started.md](getting-started.md) — Prerequisites and install (including test setup).
