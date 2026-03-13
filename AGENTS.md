# Agent guidance – wp-module-next-steps

This file gives AI agents a quick orientation to the repo. For full detail, see the **docs/** directory.

## What this project is

- **wp-module-next-steps** – A Newfold module to manage next steps for customers in a brand plugin. Plan-based tasks (tracks → sections → tasks), smart task completion, REST API under `/newfold-next-steps/v2/plans`, and React UI (widget + portal). Depends on wp-module-data and wp-module-loader. Maintained by Newfold Labs.

- **Stack:** PHP 7.3+, React (Tailwind, @newfold/ui-component-library). See docs/dependencies.md.

- **Architecture:** PlanFactory/PlanRepository, TaskCompletionTriggers, StepsApi; see docs/architecture.md and docs/api.md.

## Key paths

| Purpose | Location |
|---------|----------|
| Bootstrap | `bootstrap.php` |
| Includes | `includes/` (PlanFactory, PlanRepository, StepsApi, DTOs, data/plans) |
| React | `src/` (components, widget/portal) |
| Build | `build/` |
| Tests | `tests/` (wpunit, playwright) |

## Essential commands

```bash
composer install && npm install
npm run build
npm run start   # or npm run watch
composer test
composer test-coverage
npx playwright test
composer lint
composer fix
composer i18n
```

## Documentation

- **Full documentation** is in **docs/**. Start with **docs/index.md**. For architecture and REST API see **docs/architecture.md** and **docs/api.md**.
- **CLAUDE.md** is a symlink to this file (AGENTS.md).

---

## Keeping documentation current

When you change code, features, or workflows, update the docs. When adding or changing REST routes, update **docs/api.md**. When changing dependencies, update **docs/dependencies.md**. When cutting a release, update **docs/changelog.md**.
