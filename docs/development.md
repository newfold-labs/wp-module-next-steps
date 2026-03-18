---
name: wp-module-next-steps
title: Development
description: Lint, test, and day-to-day workflow.
updated: 2025-03-16
---

# Development

## Linting

- **PHP:** `composer lint`, `composer fix`. Uses phpcs.xml. For CI: `./lint-ci.sh`.

## Testing

- **PHPUnit/WPUnit:** `composer test`, `composer test-coverage`. Uses Codeception; requires wp-env for local runs. See `.env.testing` and `codeception.yml`.
- **Playwright E2E:** `npx playwright test`. Requires WordPress (e.g. `wp-env start`).

For full details (commands, CI workflows, PR expectations), see [testing.md](testing.md).

## Workflow

1. Make changes in `includes/` or `src/`.
2. Run `npm run build` if you changed JS; run `composer lint` and `composer test` before committing.
3. When adding or changing REST routes, update [api.md](api.md). When changing dependencies, update [dependencies.md](dependencies.md). When cutting a release, update **docs/changelog.md**.

## Coding patterns

See [architecture.md](architecture.md) for task completion handlers, DTO usage, plan data files, and constants.
