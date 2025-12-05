/**
 * Next Steps Module Test Helpers for Playwright
 * 
 * Utilities for testing the Next Steps module functionality.
 * Includes test data setup and API mocking.
 */

import { join, dirname } from 'path';
import { readFileSync, existsSync } from 'fs';
import { fileURLToPath, pathToFileURL } from 'url';

// ES module equivalent of __dirname
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Resolve plugin directory from PLUGIN_DIR env var (set by playwright.config.mjs) or process.cwd()
const pluginDir = process.env.PLUGIN_DIR || process.cwd();

// Build path to plugin helpers (.mjs extension for ES module compatibility)
const finalHelpersPath = join(pluginDir, 'tests/playwright/helpers/index.mjs');

// Verify plugin helpers exist
if (!existsSync(finalHelpersPath)) {
    throw new Error(
        `Plugin helpers not found at: ${finalHelpersPath}\n` +
        `PLUGIN_DIR: ${process.env.PLUGIN_DIR || 'not set'}\n` +
        `cwd: ${process.cwd()}`
    );
}

// Import plugin helpers using file:// URL
const helpersUrl = pathToFileURL(finalHelpersPath).href;
const pluginHelpers = await import(helpersUrl);

// Check what we actually got
if (!pluginHelpers || typeof pluginHelpers !== 'object') {
    throw new Error(
        `Plugin helpers import returned unexpected type: ${typeof pluginHelpers}.\n` +
        `Expected object with exports: auth, wordpress, newfold, a11y, utils`
    );
}

// Plugin helpers may use default export or named exports
// Dynamic imports sometimes wrap named exports in a default export
let auth, wordpress, newfold, a11y, utils;

if (pluginHelpers.default && typeof pluginHelpers.default === 'object') {
    // Has a default export - use it
    ({ auth, wordpress, newfold, a11y, utils } = pluginHelpers.default);
} else if (pluginHelpers.auth || pluginHelpers.wordpress) {
    // Has named exports directly
    ({ auth, wordpress, newfold, a11y, utils } = pluginHelpers);
} else {
    throw new Error(
        `Could not find expected exports in plugin helpers.\n` +
        `Available keys: ${Object.keys(pluginHelpers).join(', ')}\n` +
        `Default export keys: ${pluginHelpers.default ? Object.keys(pluginHelpers.default).join(', ') : 'none'}`
    );
}

if (!wordpress) {
    throw new Error(
        `Plugin helpers imported but 'wordpress' is undefined.\n` +
        `Available exports: ${Object.keys(pluginHelpers).join(', ')}\n` +
        `Default export keys: ${pluginHelpers.default ? Object.keys(pluginHelpers.default).join(', ') : 'none'}\n` +
        `Import path: ${helpersPath}\n` +
        `Plugin directory: ${pluginDir}`
    );
}

const { wpCli } = wordpress;
if (!wpCli) {
    throw new Error(
        `Plugin helpers imported but 'wordpress.wpCli' is undefined.\n` +
        `Available wordpress properties: ${Object.keys(wordpress).join(', ')}`
    );
}

// Test data fixtures
const testPlan = JSON.parse(readFileSync(join(__dirname, '../fixtures/test-plan.json'), 'utf8'));
const testCardsPlan = JSON.parse(readFileSync(join(__dirname, '../fixtures/test-cards-plan.json'), 'utf8'));

/**
 * Set next steps test fixture to database option
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function setTestNextStepsData(page) {
    await wpCli(
        `option update nfd_next_steps '${JSON.stringify(testPlan)}' --format=json`
    );
}

/**
 * Set next steps test fixture to database option (cards version)
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function setTestCardsNextStepsData(page) {
    await wpCli(
        `option update nfd_next_steps '${JSON.stringify(testCardsPlan)}' --format=json`
    );
}

/**
 * Reset test data for clean test state
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function resetNextStepsData(page) {
    await wpCli('option delete nfd_next_steps', { failOnNonZeroExit: false });
}

export {
    // Plugin helpers (re-exported for convenience)
    auth,
    wordpress,
    newfold,
    a11y,
    utils,
    // Next Steps specific helpers
    setTestNextStepsData,
    setTestCardsNextStepsData,
    resetNextStepsData
};