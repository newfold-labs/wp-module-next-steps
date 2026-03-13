# Getting started

## Prerequisites

- **PHP** 7.3+, **Node/npm**, **Composer.** The module requires wp-module-data and wp-module-loader.

## Install

```bash
composer install
npm install
```

## Build

```bash
npm run build
npm run start   # or npm run watch — development with watch
```

## Language files

```bash
composer i18n
```

## Run tests

```bash
composer test
composer test-coverage
composer test-coverage-html   # HTML report in tests/coverage/
npx playwright test           # E2E (requires WordPress environment, e.g. wp-env)
```

## Lint

```bash
composer lint
composer fix
./lint-ci.sh   # Stricter CI-level linting
```

## Using in a host plugin

1. Depend on `newfold-labs/wp-module-next-steps` (and wp-module-data, wp-module-loader).
2. The module registers with the loader and exposes REST under `/newfold-next-steps/v2/plans`. See [integration.md](integration.md) and [api.md](api.md).
