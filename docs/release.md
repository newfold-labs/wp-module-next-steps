---
name: wp-module-next-steps
title: Release process
description: Use the Newfold Prepare Release workflow to prepare releases.
updated: 2025-03-16
---

# Release process

This module follows the standard Newfold release process using a reusable workflow.

## Build step required

**Yes.** This module has a frontend (React) build. You must run `npm run build` before releasing so that `build/` contains the latest assets. The Newfold Prepare Release workflow runs the build automatically; if releasing manually, run `npm run build` after bumping versions and before tagging.

## Hardcoded versions to bump

The version must stay in sync in **two** places:

| Location | What to update |
|----------|----------------|
| **bootstrap.php** | PHP constant: `define( 'NFD_NEXTSTEPS_MODULE_VERSION', '1.5.2' );` — update the string to the new version (e.g. `1.5.3`). |
| **package.json** | Top-level `"version"` field (e.g. `"1.5.2"`). |

The reusable workflow is configured with `json-file: 'package.json'` and `php-file: 'bootstrap.php'`, so it updates both. If you bump manually, update both and then run `npm run build` and `composer i18n` (or the repo’s i18n scripts) before committing and tagging.

## Prepare release (recommended)

1. **Run the Newfold Prepare Release workflow**
   - In GitHub: **Actions** → **Newfold Prepare Release** → **Run workflow**.
   - Choose the version **level**: `patch`, `minor`, or `major`.
   - The workflow will:
     - Bump the version in **bootstrap.php** and **package.json** (see above).
     - Run a fresh **build** (`npm run build`).
     - Update **language files** (i18n).
     - Open a pull request with the changes.

2. **Review and merge** the prep-release PR.

3. **Tag and publish** the release (e.g. create a GitHub release from the tag, or follow your team's process for Satis/packagist).

## Manual release (fallback)

If the workflow is unavailable:

1. Bump version in **bootstrap.php** (`NFD_NEXTSTEPS_MODULE_VERSION`) and **package.json** (`version`).
2. Run `npm run build`.
3. Run i18n (e.g. `composer i18n` or the scripts in composer.json).
4. Commit, tag, and publish. Prefer using the workflow when possible for consistency.

## After each release

- Update **docs/changelog.md** with the changes in the release.
