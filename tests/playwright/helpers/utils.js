/**
 * Next Steps Module Test Utilities for Playwright
 * 
 * Utilities for testing the Next Steps module functionality.
 * Includes WordPress login, test data setup, and API mocking.
 */

const { expect } = require('@playwright/test');
const wordpress = require('./wordpress');

// Test data fixtures
const testPlan = require('../fixtures/test-plan.json');
const testCardsPlan = require('../fixtures/test-cards-plan.json');

/**
 * Login to WordPress
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function wpLogin(page) {
    const { loginToWordPress } = require('../../../../wordpress/content/plugins/bluehost-wordpress-plugin/tests/playwright/helpers/auth');
    await loginToWordPress(page);
}

/**
 * Set next steps test fixture to database option
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function setTestNextStepsData(page) {
    await wordpress.wpCli(
        `option update nfd_next_steps '${JSON.stringify(testPlan)}' --format=json`
    );
}

/**
 * Set next steps test fixture to database option (cards version)
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function setTestCardsNextStepsData(page) {
    await wordpress.wpCli(
        `option update nfd_next_steps '${JSON.stringify(testCardsPlan)}' --format=json`
    );
}

/**
 * Reset test data for clean test state
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function resetNextStepsData(page) {
    await wordpress.wpCli('option delete nfd_next_steps', { failOnNonZeroExit: false });
}

/**
 * Setup Next Steps API intercepts with Playwright
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function setupNextStepsIntercepts(page) {
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
 * Wait for Next Steps portal to be visible
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {number} timeout - Timeout in milliseconds (default: 25000)
 */
async function waitForNextStepsPortal(page, timeout = 25000) {
    await page.locator('#next-steps-portal').waitFor({ state: 'visible', timeout });
    await page.locator('.next-steps-fill #nfd-nextsteps').waitFor({ state: 'visible', timeout });
}

/**
 * Wait for Next Steps widget to be visible
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {number} timeout - Timeout in milliseconds (default: 25000)
 */
async function waitForNextStepsWidget(page, timeout = 25000) {
    await page.locator('#nfd_next_steps_widget').waitFor({ state: 'visible', timeout });
    await page.locator('#nfd_next_steps_widget #nfd-nextsteps').waitFor({ state: 'visible', timeout });
}

/**
 * Wait for API response and log it
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} urlPattern - URL pattern to wait for
 * @param {string} alias - Alias for the request
 */
async function waitForApiResponse(page, urlPattern, alias) {
    const response = await page.waitForResponse(response =>
        response.url().includes(urlPattern) && response.request().method() === 'POST'
    );

    const responseBody = await response.json();
    console.log(`${alias} response:`, JSON.stringify(responseBody));

    return responseBody;
}

/**
 * Wait for task endpoint response
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function waitForTaskEndpoint(page) {
    return await waitForApiResponse(page, 'newfold-next-steps', 'taskEndpoint');
}

/**
 * Wait for section endpoint response
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function waitForSectionEndpoint(page) {
    return await waitForApiResponse(page, 'newfold-next-steps', 'sectionEndpoint');
}

/**
 * Wait for track endpoint response
 * 
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function waitForTrackEndpoint(page) {
    return await waitForApiResponse(page, 'newfold-next-steps', 'trackEndpoint');
}

module.exports = {
    wpLogin,
    setTestNextStepsData,
    setTestCardsNextStepsData,
    resetNextStepsData,
    setupNextStepsIntercepts,
    waitForNextStepsPortal,
    waitForNextStepsWidget,
    waitForApiResponse,
    waitForTaskEndpoint,
    waitForSectionEndpoint,
    waitForTrackEndpoint,
};
