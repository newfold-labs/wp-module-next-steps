const { test, expect } = require('@playwright/test');
const {
    wpLogin,
    setTestNextStepsData,
    resetNextStepsData,
    setupNextStepsIntercepts,
    waitForNextStepsPortal,
    waitForTaskEndpoint,
    waitForSectionEndpoint
} = require('../helpers/utils');

test.describe('Next Steps Portal in Plugin App', () => {

    test.beforeEach(async ({ page }) => {
        await wpLogin(page);
        // Set test Next Steps data
        await setTestNextStepsData(page);
        // Set up all Next Steps API intercepts
        await setupNextStepsIntercepts(page);
        // Visit the Next Steps portal
        await page.goto(`/wp-admin/admin.php?page=${process.env.PLUGIN_ID || 'bluehost'}#/home`);
        // Reload the page to ensure the intercepts are working and updated test content is loaded
        await page.reload();

        // Portal App Renders
        await waitForNextStepsPortal(page);
    });

    test.afterAll(async ({ page }) => {
        // Reset test data
        await resetNextStepsData(page);
    });

    test('portal renders and displays correctly', async ({ page }) => {
        // Check Basic Structure
        await expect(page.locator('.nfd-track')).toHaveCount(2);
        await expect(page.locator('.nfd-section')).toHaveCount(4);
        await expect(page.locator('.nfd-nextsteps-task-container')).toHaveCount(9);

        // Check that the app has loaded with content
        await expect(page.locator('#nfd-nextsteps p')).toBeVisible();
        await expect(page.locator('#nfd-nextsteps p')).toContainText('This is a test plan');

        // Marking a task complete updates task and progress bars
        // Progress bar in first section
        await expect(page.locator('[data-nfd-section-id="section1"] .nfd-progress-bar')).toBeVisible();

        // Validate initial progress values
        await expect(page.locator('[data-nfd-section-id="section1"] .nfd-progress-bar-label')).toHaveText('0/1');
        await expect(page.locator('[data-nfd-section-id="section1"] .nfd-progress-bar-inner')).toHaveAttribute('data-percent', '0');

        // Task should be in new state
        await expect(page.locator('[data-nfd-section-id="section1"] #task-s1task1')).toHaveAttribute('data-nfd-task-status', 'new');
        await expect(page.locator('[data-nfd-section-id="section1"]')).toHaveAttribute('open');

        // Complete task
        await page.locator('#task-s1task1 .nfd-nextsteps-task-new .nfd-nextsteps-button-todo')
            .scrollIntoViewIfNeeded();
        await page.locator('#task-s1task1 .nfd-nextsteps-task-new .nfd-nextsteps-button-todo')
            .click();

        // Wait for API calls
        await waitForTaskEndpoint(page);
        await waitForSectionEndpoint(page);

        // wait for task to update and celebration to load
        await page.waitForTimeout(250);

        // Task should now be in done state
        await expect(page.locator('[data-nfd-task-id="s1task1"]')).toHaveAttribute('data-nfd-task-status', 'done');

        // Progress should update
        await expect(page.locator('.nfd-progress-bar-label').first()).toHaveText('1/1');
        await expect(page.locator('.nfd-progress-bar-inner').first()).toHaveAttribute('data-percent', '100');

        // Celebrate should be visible
        await expect(page.locator('.nfd-section-complete').first()).toBeVisible();
        await expect(page.locator('.nfd-section-celebrate-text').first()).toHaveText('All complete!');
        await expect(page.locator('.nfd-nextsteps-section-close-button').first()).toBeVisible();

        // Close celebration closes section
        await expect(page.locator('[data-nfd-section-id="section1"]')).toHaveAttribute('open');
        await page.locator('.nfd-nextsteps-section-close-button').first().click();

        await waitForSectionEndpoint(page);

        await expect(page.locator('[data-nfd-section-id="section1"] .nfd-section-celebrate')).not.toBeVisible();
        await expect(page.locator('[data-nfd-section-id="section1"] .nfd-nextsteps-task-container')).not.toBeVisible();
        await expect(page.locator('[data-nfd-section-id="section1"]')).not.toHaveAttribute('open');

        // Open the section
        await page.locator('[data-nfd-section-id="section1"] .nfd-section-header').click();

        await waitForSectionEndpoint(page);

        await expect(page.locator('[data-nfd-section-id="section1"]')).toHaveAttribute('open');
    });
});
