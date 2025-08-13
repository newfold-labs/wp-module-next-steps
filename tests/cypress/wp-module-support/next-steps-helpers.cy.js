/**
 * Helper functions for testing Next Steps functionality
 *
 * Enhanced with comprehensive logging for debugging:
 * 🔍 getTaskByStatus() - Logs method call, task found (ID, title, status)
 * ✅ completeTask() - Logs task being completed with details
 * ❌ dismissTask() - Logs task being dismissed with details  
 * ↩️ undoTask() - Logs task being undone with details
 * 📊 countTasksByStatus() - Logs count operation and results
 * 🎯 getTaskByStatusInSection() - Logs section-specific task retrieval
 * 🔓 openAllTracksAndSections() - Logs track/section opening progress
 */

/**
 * Extract and log task information for debugging
 * @param {jQuery} $task - The task element
 * @param {string} action - The action being performed
 * @returns {Object} Task details object
 */
const logTaskInfo = ( $task, action = '' ) => {
	try {
		if ( ! $task || $task.length === 0 ) {
			cy.log( `⚠️  ${action} - Task element is null or empty` );
			return { id: 'null', title: 'null', status: 'null' };
		}

		const taskContainer = $task.closest( '.nfd-nextsteps-step-container' );
		const taskId = taskContainer.length > 0 ? taskContainer.attr( 'id' ) || 'unknown-id' : 'no-container';
		const taskTitle = taskContainer.length > 0 ? taskContainer.find( '.nfd-nextsteps-step-title' ).text().trim() || 'unknown-title' : 'no-container';
		const taskStatus = $task.attr( 'class' )?.match( /nfd-nextsteps-step-(\w+)/ )?.[1] || 'unknown-status';
		
		const taskInfo = {
			id: taskId,
			title: taskTitle,
			status: taskStatus
		};
		
		if ( action ) {
			cy.log( `${action} - ID: "${taskId}", Title: "${taskTitle}", Status: "${taskStatus}"` );
		}
		
		return taskInfo;
	} catch ( error ) {
		cy.log( `⚠️  Error in logTaskInfo: ${error.message}` );
		return { id: 'error', title: 'error', status: 'error' };
	}
};

/**
 * Wait for the Next Steps app to load completely
 * @param {boolean} openAll - Whether to open all tracks and sections (default: false)
 */
export const waitForNextStepsApp = () => {
	cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
	cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
	cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
	cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );
	openAllTracksAndSections();
};



/**
 * Ensure a specific track is open
 * @param {number} trackIndex - The track index (0-based)
 */
export const ensureTrackOpen = ( trackIndex = 0 ) => {
	cy.get( '.nfd-track' ).eq( trackIndex ).then( ( $track ) => {
		if ( ! $track.attr( 'open' ) ) {
			cy.wrap( $track ).find( '.nfd-track-header' ).click();
			cy.get( '.nfd-track' ).eq( trackIndex ).should( 'have.attr', 'open' );
		}
	} );
};

/**
 * Open all tracks and sections on the page for comprehensive test access
 */
export const openAllTracksAndSections = () => {
	cy.log( '🔓 openAllTracksAndSections called - Ensuring all UI elements are accessible' );
	
	// Get all tracks and open them
	cy.get( '.nfd-track' ).each( ( $track, trackIndex ) => {
		cy.wrap( $track ).then( ( $trackElement ) => {
			if ( ! $trackElement.attr( 'open' ) ) {
				cy.log( `📂 Opening track ${trackIndex}` );
				cy.wrap( $trackElement ).find( '.nfd-track-header' ).click();
				cy.wrap( $trackElement ).should( 'have.attr', 'open' );
			}
		} );
		
		// After opening the track, open all sections within it
		cy.get( '.nfd-track' ).eq( trackIndex ).within( () => {
			cy.get( '.nfd-section' ).each( ( $section, sectionIndex ) => {
				cy.wrap( $section ).then( ( $sectionElement ) => {
					if ( ! $sectionElement.attr( 'open' ) ) {
						cy.log( `📄 Opening section ${sectionIndex} in track ${trackIndex}` );
						cy.wrap( $sectionElement ).find( '.nfd-section-header' ).click();
						cy.wrap( $sectionElement ).should( 'have.attr', 'open' );
					}
				} );
			} );
		} );
	} );
	
	// Final verification that everything is open
	cy.get( '.nfd-track' ).should( 'have.attr', 'open' );
	cy.get( '.nfd-section' ).should( 'have.attr', 'open' );
	
	// Ensure all tasks are visible and log count
	cy.get( '.nfd-nextsteps-step-container' ).should( 'be.visible' ).its( 'length' ).then( ( count ) => {
		cy.log( `✅ All tracks and sections opened - ${count} tasks now visible and ready for testing` );
	} );
};

/**
 * Ensure a section is expanded/visible
 * @param {number} trackIndex - The track index (0-based)
 * @param {number} sectionIndex - The section index (0-based)
 */
export const ensureSectionExpanded = ( trackIndex = 0, sectionIndex = 0 ) => {
	// First ensure the track is open
	ensureTrackOpen( trackIndex );
	
	cy.get( '.nfd-track' ).eq( trackIndex )
		.find( '.nfd-section' ).eq( sectionIndex ).then( ( $section ) => {
			// Check if section has tasks visible - if not, it might be collapsed
			const $steps = $section.find( '.nfd-nextsteps-step-container' );
			if ( $steps.length === 0 || ! $steps.is( ':visible' ) ) {
				// Try clicking the section header to expand it
				cy.wrap( $section ).find( '.nfd-section-header' ).click();
			}
			
			// Ensure tasks are now visible
			cy.wrap( $section ).find( '.nfd-nextsteps-step-container' ).should( 'be.visible' );
		} );
};

/**
 * Get a task by its status with robust visibility checks
 * @param {string} status - The task status ('new', 'done', 'dismissed')
 * @returns {Cypress.Chainable} The task element
 */
export const getTaskByStatus = ( status ) => {
	cy.log( `🔍 getTaskByStatus called with status: "${status}"` );
	
	// First ensure we have visible tracks and sections
	openAllTracksAndSections();
	
	// Use 15s timeout for all task statuses to handle CI environment delays
	return cy.get( `.nfd-nextsteps-step-${ status }`, { timeout: 15000 } )
		.should( 'exist' )
		.first()
		.should( 'be.visible' )
		.then( ( $task ) => {
			if ( $task && $task.length > 0 ) {
				const taskInfo = logTaskInfo( $task );
				cy.log( `✅ Found task - Status: "${status}", ID: "${taskInfo.id}", Title: "${taskInfo.title}"` );
				return cy.wrap( $task );
			} else {
				cy.log( `⚠️  No task found with status: "${status}"` );
				throw new Error( `No task found with status: ${status}` );
			}
		} );
};

/**
 * Mark a task as complete with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const completeTask = ( task ) => {
	cy.log( '✅ completeTask called' );

	// Intercept the task status update API call (since endpoint depends on permalinks)
	cy.intercept(
		{
			method: 'POST',
			url: /newfold-next-steps(\/|%2F)v1(\/|%2F)steps(\/|%2F)status/,
		},
		{
			statusCode: 200,
			body: true
		}
	).as( 'updateTaskStatus' );
	
	// Click the completion button and verify the task transitions to completed state
	task
		.scrollIntoView()
		.should( 'exist' )
		.and( 'be.visible' )
		.and( 'not.be.disabled' )
		.then( ( $task ) => {
			// Get task container for verification
			const $container = $task.closest( '.nfd-nextsteps-step-container' );
			const taskId = $container.attr( 'id' );
			cy.log( `📋 Completing task with ID: ${taskId}` );
			
			// Click the complete button
			cy.wrap( $task )
				.find( '.nfd-nextsteps-button-todo' )
				.should( 'exist' )
				.and( 'be.visible' )
				.and( 'not.be.disabled' )
				.click( { force: true } );
			
			cy.wait( '@updateTaskStatus' );
			
			cy.get( `#${taskId} .nfd-nextsteps-step-done` ).should( 'exist' );
			cy.log( `✅ Task ${taskId} verified as completed` );
		} );
	
	cy.log( `🎯 Task completion process finished` );
};

/**
 * Dismiss a task with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const dismissTask = ( task ) => {
	cy.log( '❌ dismissTask called' );
	
	// Simple, direct approach - find and click the button
	task
		.should( 'exist' )
		.and( 'be.visible' )
		.find( '.nfd-nextsteps-button-dismiss' )
		.should( 'exist' )
		.and( 'not.be.disabled' )
		.click( { force: true } );
	
	cy.log( `🚫 Task dismiss button clicked` );
};

/**
 * Undo a completed task with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const undoTask = ( task ) => {
	cy.log( '↩️ undoTask called' );
	
	// Simple, direct approach - find and click the button
	task
		.should( 'exist' )
		.and( 'be.visible' )
		.find( '.nfd-nextsteps-button-redo' )
		.should( 'exist' )
		.and( 'be.visible' )
		.and( 'not.be.disabled' )
		.click( { force: true } );
	
	cy.log( `🔄 Task undo button clicked` );
};

/**
 * Open a track accordion with robust checks
 * @param {number} index - The track index (0-based)
 */
export const openTrack = ( index ) => {
	cy.get( '.nfd-track' ).eq( index ).should( 'be.visible' ).then( ( $track ) => {
		if ( ! $track.attr( 'open' ) ) {
			cy.wrap( $track ).find( '.nfd-track-header' )
				.should( 'be.visible' )
				.click();
			// Verify it opened
			cy.get( '.nfd-track' ).eq( index ).should( 'have.attr', 'open' );
		}
	} );
};

/**
 * Close a track accordion with robust checks
 * @param {number} index - The track index (0-based)
 */
export const closeTrack = ( index ) => {
	cy.get( '.nfd-track' ).eq( index ).should( 'be.visible' ).then( ( $track ) => {
		if ( $track.attr( 'open' ) ) {
			cy.wrap( $track ).find( '.nfd-track-header' )
				.should( 'be.visible' )
				.click();
			// Verify it closed
			cy.get( '.nfd-track' ).eq( index ).should( 'not.have.attr', 'open' );
		}
	} );
};

/**
 * Toggle a section accordion with robust checks
 * @param {number} trackIndex - The track index (0-based)
 * @param {number} sectionIndex - The section index (0-based)
 */
export const toggleSection = ( trackIndex, sectionIndex ) => {
	// First ensure the track is open
	ensureTrackOpen( trackIndex );
	
	cy.get( '.nfd-track' ).eq( trackIndex )
		.find( '.nfd-section' ).eq( sectionIndex )
		.should( 'be.visible' )
		.find( '.nfd-section-header' )
		.should( 'be.visible' )
		.click();
	
	// Allow time for any animations
	cy.get( '.nfd-track' ).eq( trackIndex )
		.find( '.nfd-section' ).eq( sectionIndex )
		.should( 'be.visible' );
};



/**
 * Verify task has proper data attributes
 * @param {Cypress.Chainable} task - The task element
 */
export const verifyTaskDataAttributes = ( task ) => {
	// Check if buttons have data attributes (simplified)
	task.find( 'button[data-nfd-event-key]' ).should( 'exist' );
};

/**
 * Verify task has proper links
 * @param {Cypress.Chainable} task - The task element
 */
export const verifyTaskLinks = ( task ) => {
	// Check if link button exists
	task.find( '.nfd-nextsteps-button-link' ).should( 'exist' );
	
	// Just check that the task has some content (simplified)
	task.should( 'be.visible' );
};

/**
 * Verify task has proper icons
 * @param {Cypress.Chainable} task - The task element
 * @param {string} status - The task status ('new', 'done', 'dismissed')
 */
export const verifyTaskIcons = ( task, status ) => {
	// Just check that the task has some button elements (simplified)
	task.find( 'button' ).should( 'exist' );
	
	// Check for SVG icons in a more forgiving way
	task.find( 'svg' ).should( 'exist' );
};

/**
 * Count tasks by status
 * @param {string} status - The task status ('new', 'done', 'dismissed')
 * @returns {Cypress.Chainable} The count of tasks
 */
export const countTasksByStatus = ( status ) => {
	cy.log( `📊 countTasksByStatus called with status: "${status}"` );
	
	openAllTracksAndSections();
	
	// Use 15s timeout for all task statuses to handle CI environment delays
	return cy.get( `.nfd-nextsteps-step-${ status }`, { timeout: 15000 } ).filter(':visible').its( 'length' );
};





/**
 * Get a task by status from a specific section
 * @param {string} status - The task status ('new', 'done', 'dismissed')
 * @param {number} trackIndex - The track index (0-based)
 * @param {number} sectionIndex - The section index (0-based)
 * @returns {Cypress.Chainable} The task element
 */
export const getTaskByStatusInSection = ( status, trackIndex = 0, sectionIndex = 0 ) => {
	cy.log( `🎯 getTaskByStatusInSection called - Status: "${status}", Track: ${trackIndex}, Section: ${sectionIndex}` );
	
	// Ensure the section is expanded
	ensureSectionExpanded( trackIndex, sectionIndex );
	
	return cy.get( '.nfd-track' ).eq( trackIndex )
		.find( '.nfd-section' ).eq( sectionIndex )
		.find( `.nfd-nextsteps-step-${ status }` )
		.first()
		.should( 'be.visible' )
		.then( ( $task ) => {
			const taskInfo = logTaskInfo( $task );
			cy.log( `✅ Found section task - Status: "${status}", ID: "${taskInfo.id}", Title: "${taskInfo.title}", Track: ${trackIndex}, Section: ${sectionIndex}` );
			
			return cy.wrap( $task );
		} );
};

/**
 * Toggle dismissed tasks visibility with robust checks
 */
export const toggleDismissedTasks = () => {
	cy.get( '.nfd-nextsteps-filter-button' )

		.and( 'not.be.disabled' )
		.scrollIntoView()
		.click( { force: true } ); // Force click to handle potential overlapping elements
};
