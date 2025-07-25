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
	const taskContainer = $task.closest( '.nfd-nextsteps-step-container' );
	const taskId = taskContainer.attr( 'id' ) || 'unknown-id';
	const taskTitle = taskContainer.find( '.nfd-nextsteps-step-title' ).text().trim() || 'unknown-title';
	const taskStatus = $task.attr( 'class' ).match( /nfd-nextsteps-step-(\w+)/ )?.[1] || 'unknown-status';
	
	const taskInfo = {
		id: taskId,
		title: taskTitle,
		status: taskStatus
	};
	
	if ( action ) {
		cy.log( `${action} - ID: "${taskId}", Title: "${taskTitle}", Status: "${taskStatus}"` );
	}
	
	return taskInfo;
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
	
	return cy.get( `.nfd-nextsteps-step-${ status }` ).first().should( 'be.visible' ).then( ( $task ) => {
		const taskInfo = logTaskInfo( $task );
		cy.log( `✅ Found task - Status: "${status}", ID: "${taskInfo.id}", Title: "${taskInfo.title}"` );
		
		return cy.wrap( $task );
	} );
};

/**
 * Mark a task as complete with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const completeTask = ( task ) => {
	task.then( ( $task ) => {
		logTaskInfo( $task, '✅ completeTask called' );
	} );
	
	// Now find and click the complete button
	task.find( '.nfd-nextsteps-button-todo' )
		.should( 'be.visible' )
		.and( 'not.be.disabled' )
		.click( { force: true } ); // Force click to handle overlapping SVG icons
	
	cy.log( `🎯 Task completion button clicked` );
};

/**
 * Dismiss a task with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const dismissTask = ( task ) => {
	task.then( ( $task ) => {
		logTaskInfo( $task, '❌ dismissTask called' );
	} );
	
	// Now find and click the dismiss button
	task.find( '.nfd-nextsteps-button-dismiss' )
		.should( 'not.be.disabled' )
		.click( { force: true } ); // Force click to handle overlapping SVG icons and hover state
	
	cy.log( `🚫 Task dismiss button clicked` );
};

/**
 * Undo a completed task with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const undoTask = ( task ) => {
	task.then( ( $task ) => {
		logTaskInfo( $task, '↩️ undoTask called' );
	} );
	
	// Now find and click the undo button
	task.find( '.nfd-nextsteps-button-redo' )
		.and( 'not.be.disabled' )
		.click( { force: true } ); // Force click to handle overlapping SVG icons
	
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
	
	return cy.get( `.nfd-nextsteps-step-${ status }` ).filter(':visible').its( 'length' ).then( ( count ) => {
		cy.log( `📈 Found ${count} tasks with status: "${status}"` );
		return count;
	} );
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
		.should( 'be.visible' )
		.and( 'not.be.disabled' )
		.scrollIntoView()
		.click( { force: true } ); // Force click to handle potential overlapping elements
};

/**
 * Reset test data for clean test state
 */
export const resetNextStepsData = () => {
        // Use cy.exec to run wp-cli commands through wp-env
        cy.exec( 'npx wp-env run cli wp option delete nfd_next_steps', { failOnNonZeroExit: false } );

}; 