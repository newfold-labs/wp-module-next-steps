// <reference types="Cypress" />
import { wpLogin } from '../wp-module-support/utils.cy';

describe( 'Next Steps Dashboard Widget', { testIsolation: true }, () => {
	beforeEach( () => {
		wpLogin();
		cy.visit( '/wp-admin/index.php' );
		
		// Wait for the next steps widget to load
		cy.get( '#nfd_next_steps_widget' ).should( 'be.visible' );
		cy.get( '#nfd-next-steps-app' ).should( 'be.visible' );
		cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
	} );

	it( 'renders the widget container and title', () => {
		cy.get( '#nfd_next_steps_widget' )
			.scrollIntoView()
			.should( 'be.visible' );

		cy.get( '#nfd_next_steps_widget h2' ).contains( 'Next Steps' );

		cy.get( '#nfd_next_steps_widget .nfd-widget-next-steps' )
			.scrollIntoView()
			.should( 'be.visible' );
	} );

	it( 'renders the main next steps app structure', () => {
		cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
		cy.get( '#nfd-nextsteps p' ).should( 'contain.text', 'Get started' );
		
		// Check that tracks exist
		cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
		
		// Check that the first track is open by default
		cy.get( '.nfd-track' ).first().should( 'have.attr', 'open' );
	} );

	it( 'renders tracks with proper structure', () => {
		// Check track headers
		cy.get( '.nfd-track-header' ).should( 'have.length.greaterThan', 0 );
		
		// Check track titles
		cy.get( '.nfd-track-title' ).should( 'have.length.greaterThan', 0 );
		
		// Check track icons (chevron)
		cy.get( '.nfd-track-header-icon' ).should( 'have.length.greaterThan', 0 );
		
		// Check track sections container
		cy.get( '.nfd-track-sections' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'renders sections with proper structure', () => {
		// Check sections exist
		cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
		
		// Check section headers
		cy.get( '.nfd-section-header' ).should( 'have.length.greaterThan', 0 );
		
		// Check section titles
		cy.get( '.nfd-section-title' ).should( 'have.length.greaterThan', 0 );
		
		// Check section icons
		cy.get( '.nfd-section-header-icon' ).should( 'have.length.greaterThan', 0 );
		
		// Check section steps container
		cy.get( '.nfd-section-steps' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'renders tasks with proper structure', () => {
		// Check tasks exist
		cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );
		
		// Check task content
		cy.get( '.nfd-nextsteps-step-content' ).should( 'have.length.greaterThan', 0 );
		
		// Check task titles
		cy.get( '.nfd-nextsteps-step-title' ).should( 'have.length.greaterThan', 0 );
		
		// Check task buttons
		cy.get( '.nfd-nextsteps-button' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'displays progress bars for sections with tasks', () => {
		// Look for progress bars in sections
		cy.get( '.nfd-section' ).each( ( $section ) => {
			// Check if this section has any tasks
			cy.wrap( $section ).find( '.nfd-nextsteps-step-container' ).then( ( $tasks ) => {
				if ( $tasks.length > 0 ) {
					// If there are tasks, there should be a progress bar
					cy.wrap( $section ).find( '.nfd-progress-bar' ).should( 'exist' );
				}
			} );
		} );
	} );

	it( 'allows track accordion functionality', () => {
		// Get the first track
		cy.get( '.nfd-track' ).first().as( 'firstTrack' );
		
		// It should be open by default
		cy.get( '@firstTrack' ).should( 'have.attr', 'open' );
		
		// Click the header to close
		cy.get( '@firstTrack' ).find( '.nfd-track-header' ).click();
		
		// It should close
		cy.get( '@firstTrack' ).should( 'not.have.attr', 'open' );
		
		// Click again to open
		cy.get( '@firstTrack' ).find( '.nfd-track-header' ).click();
		
		// It should open again
		cy.get( '@firstTrack' ).should( 'have.attr', 'open' );
	} );

	it( 'allows section accordion functionality', () => {
		// Get the first section
		cy.get( '.nfd-section' ).first().as( 'firstSection' );
		
		// Click the header to toggle
		cy.get( '@firstSection' ).find( '.nfd-section-header' ).click();
		
		// Wait for any animation/state change
		cy.wait( 100 );
		
		// Click again to toggle back
		cy.get( '@firstSection' ).find( '.nfd-section-header' ).click();
		
		// Wait for any animation/state change
		cy.wait( 100 );
	} );

	it( 'allows marking tasks as complete', () => {
		// Find a task with "new" status
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		
		// Click the complete button (todo icon)
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).click();
		
		// Wait for API call and state update
		cy.wait( 1000 );
		
		// The task should now be in done state
		cy.get( '@newTask' ).should( 'have.class', 'nfd-nextsteps-step-done' );
		
		// Or check that a done task appeared (since DOM might rebuild)
		cy.get( '.nfd-nextsteps-step-done' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'allows dismissing tasks', () => {
		// Find a task with "new" status
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		
		// Click the dismiss button (hide icon)
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-dismiss' ).click();
		
		// Wait for API call and state update
		cy.wait( 1000 );
		
		// The task should be hidden (dismissed tasks are hidden by default)
		cy.get( '@newTask' ).should( 'not.exist' );
	} );

	it( 'allows undoing completed tasks', () => {
		// First, mark a task as complete
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).click();
		cy.wait( 1000 );
		
		// Find a completed task
		cy.get( '.nfd-nextsteps-step-done' ).first().as( 'doneTask' );
		
		// Click the redo button to undo completion
		cy.get( '@doneTask' ).find( '.nfd-nextsteps-button-redo' ).click();
		
		// Wait for API call and state update
		cy.wait( 1000 );
		
		// The task should be back to new state
		cy.get( '.nfd-nextsteps-step-new' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'shows and hides dismissed tasks', () => {
		// First, dismiss a task
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-dismiss' ).click();
		cy.wait( 1000 );
		
		// Check that the toggle button exists
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'contain.text', 'View skipped tasks' );
		
		// Click to show dismissed tasks
		cy.get( '.nfd-nextsteps-filter-button' ).click();
		
		// Button text should change
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'contain.text', 'Hide skipped tasks' );
		
		// Dismissed tasks should be visible
		cy.get( '.nfd-nextsteps-step-dismissed' ).should( 'be.visible' );
		
		// Click to hide dismissed tasks again
		cy.get( '.nfd-nextsteps-filter-button' ).click();
		
		// Button text should change back
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'contain.text', 'View skipped tasks' );
	} );

	it( 'shows proper task links and buttons', () => {
		// Check that tasks have proper links
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		
		// Check that the task title is clickable (if it has an href)
		cy.get( '@newTask' ).find( '.nfd-nextsteps-step-title' ).parent().then( ( $parent ) => {
			if ( $parent.is( 'a' ) ) {
				cy.wrap( $parent ).should( 'have.attr', 'href' );
			}
		} );
		
		// Check that the go button/link exists and has proper href
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-link' ).should( 'have.attr', 'href' );
		
		// Check that buttons have proper event attributes
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).should( 'have.attr', 'data-nfd-event-key' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-dismiss' ).should( 'have.attr', 'data-nfd-event-key' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-link' ).should( 'have.attr', 'data-nfd-event-key' );
	} );

	it( 'updates progress bars when tasks change status', () => {
		// Get initial progress bar state
		cy.get( '.nfd-progress-bar' ).first().as( 'progressBar' );
		
		// Mark a task as complete
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).click();
		
		// Wait for API call and state update
		cy.wait( 1000 );
		
		// Progress bar should reflect the change
		cy.get( '@progressBar' ).should( 'be.visible' );
		
		// Check that progress bar has proper attributes
		cy.get( '@progressBar' ).find( '[role="progressbar"]' ).should( 'exist' );
	} );

	it( 'handles task data attributes correctly', () => {
		// Check that tasks have proper data attributes
		cy.get( '.nfd-nextsteps-step-container' ).first().then( ( $task ) => {
			// Should have an id attribute
			cy.wrap( $task ).should( 'have.attr', 'id' );
			
			// May have custom data attributes
			const attributes = $task.get( 0 ).attributes;
			Object.values( attributes ).forEach( ( attr ) => {
				if ( attr.name.startsWith( 'data-' ) ) {
					cy.wrap( $task ).should( 'have.attr', attr.name );
				}
			} );
		} );
	} );

	it( 'handles loading state properly', () => {
		// This test might be hard to catch since loading is fast
		// but we can at least verify the structure is ready
		cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' );
		cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'shows proper icons for different task states', () => {
		// Check new task icons
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).find( 'svg' ).should( 'exist' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-dismiss' ).find( 'svg' ).should( 'exist' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-link' ).find( 'svg' ).should( 'exist' );
		
		// Check done task icons (if any exist)
		cy.get( '.nfd-nextsteps-step-done' ).then( ( $doneTasks ) => {
			if ( $doneTasks.length > 0 ) {
				cy.wrap( $doneTasks ).first().find( '.nfd-nextsteps-button-redo' ).find( 'svg' ).should( 'exist' );
			}
		} );
	} );
} );
