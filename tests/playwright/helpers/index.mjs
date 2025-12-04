/**
 * Next Steps Module Test Helpers for Playwright
 * 
 * Utilities for testing the Next Steps module functionality.
 * Includes test data setup and API mocking.
 */

import { resolve, join } from 'path';
import { readFileSync, existsSync } from 'fs';
import { fileURLToPath } from 'url';
import { dirname } from 'path';

// ES module equivalent of __dirname
const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Use environment variable to resolve plugin helpers (set by playwright.config.mjs)
// but if it's not set, use fallback path
const pluginDir = process.env.PLUGIN_DIR || resolve(__dirname, '../../../../../../');

// Verify we can find the plugin helpers (will throw clear error if path is wrong)
const helpersPath = join(pluginDir, 'tests/playwright/helpers/index.js');

// Check if the file exists before trying to import
if (!existsSync(helpersPath)) {
    throw new Error(
        `Plugin helpers file not found at: ${helpersPath}\n` +
        `Plugin directory: ${pluginDir}\n` +
        `Module __dirname: ${__dirname}\n` +
        `PLUGIN_DIR env var: ${process.env.PLUGIN_DIR || 'not set'}`
    );
}

// Load plugin helpers and Playwright (to ensure single instance)
let pluginHelpers;
try {
    pluginHelpers = await import(helpersPath);
} catch (error) {
    throw new Error(
        `Failed to import plugin helpers from ${helpersPath}.\n` +
        `Plugin directory: ${pluginDir}\n` +
        `Error: ${error.message}`
    );
}

// Check what we actually got
if (!pluginHelpers || typeof pluginHelpers !== 'object') {
    throw new Error(
        `Plugin helpers import returned unexpected type: ${typeof pluginHelpers}.\n` +
        `Expected object with exports: auth, wordpress, newfold, a11y, utils`
    );
}

const { auth, wordpress, newfold, a11y, utils } = pluginHelpers;

if (!wordpress) {
    throw new Error(
        `Plugin helpers imported but 'wordpress' is undefined.\n` +
        `Available exports: ${Object.keys(pluginHelpers).join(', ')}\n` +
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

// Import Playwright from plugin to avoid double-loading
const { test, expect } = await import(join(pluginDir, 'node_modules/@playwright/test/index.js'));

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

// Track fulfilled requests for wait functions
const fulfilledRequests = new Map();

/**
 * Setup Next Steps API intercepts with Playwright
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function setupNextStepsIntercepts(page) {
    // Clear previous fulfilled requests
    fulfilledRequests.clear();
    // Intercept data event endpoint
    await page.route('**/newfold-data*/v1/events/**', async (route) => {
        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify(true)
        });
    });

    // Intercept the task status update API call
    await page.route('**/newfold-next-steps*/v2/plans*/tasks/**', async (route) => {
        const url = route.request().url();
        const taskIdMatch = url.match(/\/tasks\/([^\/\?]+)/);
        const taskId = taskIdMatch ? taskIdMatch[1] : 's1task1';

        const requestBody = route.request().postDataJSON();
        const taskStatus = requestBody?.status || 'done';

        const response = {
            id: taskId,
            status: taskStatus
        };

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify(response)
        });
    });

    // Intercept the section update API call
    await page.route('**/newfold-next-steps*/v2/plans*/sections/**', async (route) => {
        const url = route.request().url();

        // Extract section ID from URL - handle different URL structures
        let sectionIdMatch = url.match(/\/sections\/([^\/\?]+)/);
        if (!sectionIdMatch) {
            sectionIdMatch = url.match(/sections%2F([^%&]+)/); // URL encoded
        }
        if (!sectionIdMatch) {
            sectionIdMatch = url.match(/sections\/([^\/\?&]+)/);
        }
        const sectionId = sectionIdMatch ? sectionIdMatch[1] : 'not-found';

        const requestBody = route.request().postDataJSON();
        const response = {
            id: sectionId
        };

        // Add status and date_completed for status updates
        if (requestBody?.type === 'status') {
            response.status = requestBody.value;
            if (requestBody.value === 'done' || requestBody.value === 'dismissed') {
                response.date_completed = new Date().toISOString().slice(0, 19).replace('T', ' ');
            }
        }

        // Add open state for open updates
        if (requestBody?.type === 'open') {
            response.open = requestBody.value;
        }

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify(response)
        });
    });

    // Intercept the track update API call
    await page.route('**/newfold-next-steps*/v2/plans*/tracks/**', async (route) => {
        const url = route.request().url();
        const trackIdMatch = url.match(/\/tracks\/([^\/\?]+)/);
        const trackId = trackIdMatch ? trackIdMatch[1] : 'track1';

        const requestBody = route.request().postDataJSON();
        const response = {
            id: trackId,
            open: requestBody?.open
        };

        await route.fulfill({
            status: 200,
            contentType: 'application/json',
            body: JSON.stringify(response)
        });
    });
}

/**
 * Wait for API request (works with intercepted requests)
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} urlPattern - URL pattern to wait for
 * @param {string} alias - Alias for the request
 * @param {string} method - HTTP method to wait for (default: 'POST')
 */
async function waitForApiResponse(page, urlPattern, alias, method = 'POST', timeout = 30000) {
    try {
        // Wait for the request to be made (intercepts will fulfill it)
        const request = await page.waitForRequest(request => {
            const url = request.url();
            const reqMethod = request.method();
            const matches = url.includes(urlPattern) && reqMethod === method;
            if (matches) {
                console.log(`${alias} request matched: ${reqMethod} ${url}`);
            }
            return matches;
        }, { timeout });

        console.log(`${alias} request intercepted: ${request.method()} ${request.url()}`);

        // Try to get response body if available (for intercepted requests, we may not have a real response)
        try {
            const response = await request.response();
            if (response) {
                const responseBody = await response.json();
                console.log(`${alias} response:`, JSON.stringify(responseBody));
                return responseBody;
            }
        } catch (e) {
            // Response may not be available if intercepted, that's okay
            console.log(`${alias} request fulfilled by intercept`);
        }

        return { intercepted: true, url: request.url() };
    } catch (error) {
        console.log(`${alias} timeout - request not found within ${timeout}ms`);
        throw error;
    }
}

/**
 * Wait for task endpoint response
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function waitForTaskEndpoint(page) {
    return await waitForApiResponse(page, '/tasks/', 'taskEndpoint');
}

/**
 * Wait for section endpoint response
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {number} timeout - Timeout in milliseconds (default: 10000)
 * @param {boolean} optional - If true, don't throw on timeout (default: false)
 */
async function waitForSectionEndpoint(page, timeout = 10000, optional = false) {
    try {
        return await waitForApiResponse(page, '/sections/', 'sectionEndpoint', 'POST', timeout);
    } catch (error) {
        if (optional) {
            console.log('sectionEndpoint wait optional - continuing');
            return null;
        }
        throw error;
    }
}

/**
 * Wait for track endpoint response
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function waitForTrackEndpoint(page) {
    return await waitForApiResponse(page, '/tracks/', 'trackEndpoint');
}

export {
    // Re-export Playwright from plugin to ensure single instance
    test,
    expect,
    // Plugin helpers (re-exported for convenience)
    auth,
    wordpress,
    newfold,
    a11y,
    utils,
    // Next Steps specific helpers
    setTestNextStepsData,
    setTestCardsNextStepsData,
    resetNextStepsData,
    setupNextStepsIntercepts,
    waitForApiResponse,
    waitForTaskEndpoint,
    waitForSectionEndpoint,
    waitForTrackEndpoint,
};