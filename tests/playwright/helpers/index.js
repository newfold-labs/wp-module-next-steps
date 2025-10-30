/**
 * Next Steps Module Test Helpers for Playwright
 * 
 * Utilities for testing the Next Steps module functionality.
 * Includes test data setup and API mocking.
 */

const path = require('path');

// Use environment variable to resolve plugin helpers
const pluginDir = process.env.PLUGIN_DIR || path.resolve(__dirname, '../../../../../../');
const { wordpress } = require(path.join(pluginDir, 'tests/playwright/helpers'));
const { wpCli } = wordpress;

// Test data fixtures
const testPlan = require('../fixtures/test-plan.json');
const testCardsPlan = require('../fixtures/test-cards-plan.json');

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

module.exports = {
    setTestNextStepsData,
    setTestCardsNextStepsData,
    resetNextStepsData,
    setupNextStepsIntercepts,
    waitForApiResponse,
    waitForTaskEndpoint,
    waitForSectionEndpoint,
    waitForTrackEndpoint,
};