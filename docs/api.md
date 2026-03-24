---
name: wp-module-next-steps
title: REST API
description: Endpoints table for /newfold-next-steps/v2/plans.
updated: 2025-03-16
---

# REST API

Base namespace: `/wp-json/newfold-next-steps/v2/plans`. All endpoints require `manage_options` capability. Controller: `includes/StepsApi.php`.

| Method | Path | Description |
|--------|------|-------------|
| GET | /plans | Get current plan |
| POST | /plans/add | Add tasks to plan |
| PUT | /plans/tasks/{task_id} | Update task status |
| PUT | /plans/sections/{section_id} | Update section state |
| PUT | /plans/tracks/{track_id} | Update track open state |
| GET | /plans/stats | Get completion statistics |
| PUT | /plans/switch | Switch plan type |
| PUT | /plans/reset | Reset to defaults |
