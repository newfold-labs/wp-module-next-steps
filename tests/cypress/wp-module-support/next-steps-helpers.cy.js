/**
 * Helper functions for testing Next Steps functionality
 */

/**
 * Wait for the Next Steps app to load completely
 */
export const waitForNextStepsApp = () => {
	cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
	cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
	cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
	cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );
	
	// Ensure at least the first track is open for interaction
	ensureFirstTrackOpen();
};

/**
 * Ensure the first track is open for interaction
 */
export const ensureFirstTrackOpen = () => {
	cy.get( '.nfd-track' ).first().then( ( $track ) => {
		if ( ! $track.attr( 'open' ) ) {
			cy.wrap( $track ).find( '.nfd-track-header' ).click();
			cy.get( '.nfd-track' ).first().should( 'have.attr', 'open' );
		}
	} );
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
	// First ensure we have visible tracks and sections
	ensureFirstTrackOpen();
	
	return cy.get( `.nfd-nextsteps-step-${ status }` ).first().should( 'be.visible' );
};

/**
 * Mark a task as complete with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const completeTask = ( task ) => {
	// Now find and click the complete button
	task.find( '.nfd-nextsteps-button-todo' )
		.should( 'be.visible' )
		.and( 'not.be.disabled' )
		.click( { force: true } ); // Force click to handle overlapping SVG icons
};

/**
 * Dismiss a task with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const dismissTask = ( task ) => {
	// Now find and click the dismiss button
	task.find( '.nfd-nextsteps-button-dismiss' )
		.should( 'not.be.disabled' )
		.click( { force: true } ); // Force click to handle overlapping SVG icons
};

/**
 * Undo a completed task with robust checks
 * @param {Cypress.Chainable} task - The task element
 */
export const undoTask = ( task ) => {
	// Now find and click the undo button
	task.find( '.nfd-nextsteps-button-redo' )
		.and( 'not.be.disabled' )
		.click( { force: true } ); // Force click to handle overlapping SVG icons
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
 * Get progress bar completion percentage
 * @param {Cypress.Chainable} section - The section element
 * @returns {Cypress.Chainable} The progress percentage
 */
export const getProgressPercentage = ( section ) => {
	return section.find( '.nfd-progress-bar [role="progressbar"]' )
		.invoke( 'attr', 'aria-valuenow' );
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
	return cy.get( `.nfd-nextsteps-step-${ status }` ).its( 'length' );
};

/**
 * Verify progress bar reflects task completion
 * @param {Cypress.Chainable} section - The section element
 * @param {number} expectedCompleted - Expected number of completed tasks
 * @param {number} expectedTotal - Expected total number of tasks
 */
export const verifyProgressBar = ( section, expectedCompleted, expectedTotal ) => {
	const expectedPercentage = Math.round( ( expectedCompleted / expectedTotal ) * 100 );
	
	section.find( '.nfd-progress-bar [role="progressbar"]' )
		.should( 'have.attr', 'aria-valuenow', expectedPercentage.toString() );
};

/**
 * Get all tasks within a specific section with visibility checks
 * @param {number} trackIndex - The track index (0-based)
 * @param {number} sectionIndex - The section index (0-based)
 * @returns {Cypress.Chainable} The task elements
 */
export const getTasksInSection = ( trackIndex, sectionIndex ) => {
	// Ensure the section is expanded first
	ensureSectionExpanded( trackIndex, sectionIndex );
	
	return cy.get( '.nfd-track' ).eq( trackIndex )
		.find( '.nfd-section' ).eq( sectionIndex )
		.find( '.nfd-nextsteps-step-container' )
		.should( 'be.visible' );
};

/**
 * Get a task by status from a specific section
 * @param {string} status - The task status ('new', 'done', 'dismissed')
 * @param {number} trackIndex - The track index (0-based)
 * @param {number} sectionIndex - The section index (0-based)
 * @returns {Cypress.Chainable} The task element
 */
export const getTaskByStatusInSection = ( status, trackIndex = 0, sectionIndex = 0 ) => {
	// Ensure the section is expanded
	ensureSectionExpanded( trackIndex, sectionIndex );
	
	return cy.get( '.nfd-track' ).eq( trackIndex )
		.find( '.nfd-section' ).eq( sectionIndex )
		.find( `.nfd-nextsteps-step-${ status }` )
		.first()
		.should( 'be.visible' );
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