/**
 * Cypress API Intercept Helpers for Next Steps Module
 * 
 * This file contains the main intercept function for the Next Steps API endpoints.
 * Use setupNextStepsIntercepts() in your test files to ensure consistent API mocking.
 */

/**
 * Sets up all Next Steps API intercepts with dynamic responses
 * Call this function in your beforeEach() hook
 */
export function setupNextStepsIntercepts() {
	// Intercept data event endpoint as @dataEndpoint
	cy.intercept(
		{
			method: 'POST',
			url: /.*newfold-data.*v1.*events.*/,
		},
		(req) => {
			req.reply({
				statusCode: 200,
				body: true
			});
		}
	).as( 'dataEndpoint' );

	// Intercept the task status update API call as @taskEndpoint
	cy.intercept(
		{
			method: 'POST',
			url: /.*newfold-next-steps.*v2.*plans.*tasks.*/,
		},
		(req) => {
			// Extract task ID from URL - handle different URL structures
			const taskIdMatch = req.url.match(/\/tasks\/([^\/\?]+)/);
			const taskId = taskIdMatch ? taskIdMatch[1] : 's1task1';
			const taskStatus = req.body.status || 'done';
			const response = {
				id: taskId,
				status: taskStatus
			};

			req.reply({
				statusCode: 200,
				body: response
			});
		}
	).as( 'taskEndpoint' );

	// Intercept the section update API call as @sectionEndpoint
	cy.intercept(
		{
			method: 'POST',
			url: /.*newfold-next-steps.*v2.*plans.*sections.*/,
		},
		(req) => {
			// Extract section ID from URL - handle different URL structures
			// Try multiple patterns to catch different URL formats
			let sectionIdMatch = req.url.match(/\/sections\/([^\/\?]+)/);
			if (!sectionIdMatch) {
				// Try alternative pattern in case URL structure is different
				sectionIdMatch = req.url.match(/sections%2F([^%&]+)/); // URL encoded
			}
			if (!sectionIdMatch) {
				// Try another pattern
				sectionIdMatch = req.url.match(/sections\/([^\/\?&]+)/);
			}
			const sectionId = sectionIdMatch ? sectionIdMatch[1] : 'not-found';
			const response = {
				id: sectionId
			};
			
			// Add status and date_completed for status updates
			if (req.body.type === 'status') {
				response.status = req.body.value;
				if (req.body.value === 'done' || req.body.value === 'dismissed') {
					response.date_completed = new Date().toISOString().slice(0, 19).replace('T', ' ');
				}
			}

			// Add open state for open updates
			if (req.body.type === 'open') {
				response.open = req.body.value;
			}

			req.reply({
				statusCode: 200,
				body: response
			});
		}
	).as( 'sectionEndpoint' );

	// Intercept the track update API call as @trackEndpoint
	cy.intercept(
		{
			method: 'POST',
			url: /.*newfold-next-steps.*v2.*plans.*tracks.*/,
		},
		(req) => {
			// Extract track ID from URL - handle different URL structures
			const trackIdMatch = req.url.match(/\/tracks\/([^\/\?]+)/);
			const trackId = trackIdMatch ? trackIdMatch[1] : 'track1';

			const response = {
				id: trackId,
				open: req.body.open
			};

			req.reply({
				statusCode: 200,
				body: response
			});
		}
	).as( 'trackEndpoint' );
}
