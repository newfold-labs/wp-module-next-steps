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

	// Intercept the task status update API call as @taskEndpoint
	cy.intercept(
		{
			method: 'POST',
			url: /.*newfold-next-steps.*v2.*plans.*tasks.*/,
		},
		(req) => {
			// Extract task ID from URL
			const taskId = req.url.match(/\/tasks\/([^\/\?]+)/)[1];
			console.log('Task intercept matched:', req.url, 'Task ID:', taskId);
			req.reply({
				statusCode: 200,
				body: {
					id: taskId,
					status: req.body.status || 'done'
				}
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
			// Extract section ID from URL
			const sectionId = req.url.match(/\/sections\/([^\/\?]+)/)[1];
			console.log('Section intercept matched:', req.url, 'Section ID:', sectionId);
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
			// Extract track ID from URL
			const trackId = req.url.match(/\/tracks\/([^\/\?]+)/)[1];
			console.log('Track intercept matched:', req.url, 'Track ID:', trackId);
			req.reply({
				statusCode: 200,
				body: {
					id: trackId,
					open: req.body.open
				}
			});
		}
	).as( 'trackEndpoint' );
}
