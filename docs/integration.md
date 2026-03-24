---
name: wp-module-next-steps
title: Integration
description: How the module registers and integrates with onboarding/solutions.
updated: 2025-03-16
---

# Integration

## How the module registers

The module registers with the Newfold Module Loader via bootstrap.php. It provides plan management (PlanFactory, PlanRepository), smart task completion (TaskCompletionTriggers, TaskStateValidator), and REST API (StepsApi). The host plugin loads the module and typically mounts the React widget/portal in the admin.

## Module integration

This module integrates with:

- **Onboarding Module** – Listens for site type selection (`nfd_module_onboarding_site_info`).
- **Solutions Module** – Provides task recommendations.
- **Data Module** – Analytics and tracking.
- **Brand Plugins** – Bluehost, HostGator specific features.

## Dependencies

See [dependencies.md](dependencies.md).
