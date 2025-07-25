// <reference types="Cypress" />
import { wpLogin, wpCli } from '../wp-module-support/utils.cy';
import {
	resetNextStepsData,
	waitForNextStepsApp,
	getTaskByStatus,
	completeTask,
	dismissTask,
	countTasksByStatus,
	openTrack,
	closeTrack
} from '../wp-module-support/next-steps-helpers.cy';

describe( 'Next Steps Dashboard Widget', { testIsolation: true }, () => {
	before( () => {
		// Reset Next Steps data to ensure clean state for tests
		resetNextStepsData();
	} );

	beforeEach( () => {
		wpLogin();		
		cy.visit( '/wp-admin/index.php' );
		
		// Wait for the next steps widget to load
		cy.get( '#nfd_next_steps_widget' ).should( 'be.visible' );
		cy.get( '#nfd-next-steps-app' ).should( 'be.visible' );
		waitForNextStepsApp();
	} );

	it( 'renders complete structure and elements correctly', () => {
		// Widget Container and Title
		cy.get( '#nfd_next_steps_widget' )
			.scrollIntoView()
			.should( 'be.visible' );

		cy.get( '#nfd_next_steps_widget h2' ).contains( 'Next Steps' );

		cy.get( '#nfd_next_steps_widget .nfd-widget-next-steps' )
			.scrollIntoView()
			.should( 'be.visible' );

		// Main Next Steps App Structure
		cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' );
		
		// Check that tracks exist
		cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
		
		// Check that the first track is open by default
		cy.get( '.nfd-track' ).first().should( 'have.attr', 'open' );

		// Tracks Structure
		// Check track headers
		cy.get( '.nfd-track-header' ).should( 'have.length.greaterThan', 0 );
		
		// Check track titles
		cy.get( '.nfd-track-title' ).should( 'have.length.greaterThan', 0 );
		
		// Check track icons (chevron)
		cy.get( '.nfd-track-header-icon' ).should( 'have.length.greaterThan', 0 );
		
		// Check track sections container
		cy.get( '.nfd-track-sections' ).should( 'have.length.greaterThan', 0 );

		// Sections Structure
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

		// Tasks Structure
		// Check tasks exist
		cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );
		
		// Check task content
		cy.get( '.nfd-nextsteps-step-content' ).should( 'have.length.greaterThan', 0 );
		
		// Check task titles
		cy.get( '.nfd-nextsteps-step-title' ).should( 'have.length.greaterThan', 0 );
		
		// Check task buttons
		cy.get( '.nfd-nextsteps-button' ).should( 'have.length.greaterThan', 0 );

		// Task Data Attributes
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

		// Loading State Verification
		// Verify the structure is ready and loaded
		cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' );
		cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'displays progress bars and visual elements correctly', () => {
		// Progress Bars for Sections
		// With clean state, verify that sections with tasks display progress bars
		cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
		
		// Check if progress bars exist for sections with tasks
		cy.get( '.nfd-progress-bar' ).should( 'have.length.greaterThan', 0 );
		
		// Each progress bar should have proper structure
		cy.get( '.nfd-progress-bar' ).each( ( $progressBar ) => {
			cy.wrap( $progressBar ).find( '.nfd-progress-bar-label' ).should( 'exist' );
			cy.wrap( $progressBar ).find( '.nfd-progress-bar-inner' ).should( 'exist' );
			
			// Progress bar should show completion ratio (e.g., "0/5" for fresh state)
			cy.wrap( $progressBar ).find( '.nfd-progress-bar-label' ).should( 'contain', '/' );
		} );
		
		// Verify that tasks exist in the clean state
		cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );

		// ensure the first track is open
		openTrack( 0 );
		
		// Task Icons for Different States
		// Check new task icons
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).find( 'svg' ).should( 'exist' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-dismiss' ).find( 'svg' ).should( 'exist' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-link' ).find( 'svg' ).should( 'exist' );
		
		// Complete a task to test done task icons
		getTaskByStatus( 'new' ).first().then( ( task ) => {
			completeTask( cy.wrap( task ) );
		} );
		
		// Check that the completed task has the correct redo icon
		getTaskByStatus( 'done' ).first().find( '.nfd-nextsteps-button-redo' ).find( 'svg' ).should( 'exist' );

		// Progress Bar Updates
		// Mark a task as complete using helper function
		getTaskByStatus( 'new' ).first().then( ( task ) => {
			completeTask( cy.wrap( task ) );
		} );
		
		// Check that the task state changed (this is the main functionality)
		getTaskByStatus( 'done' ).should( 'have.length.greaterThan', 0 );
		
		// Check if progress elements exist anywhere in the app
		cy.get( '#nfd-nextsteps' ).find( '.nfd-progress-bar, [role="progressbar"]' ).should( 'exist' );
	} );

	it( 'handles all interaction functionality correctly', () => {
		// Ensure Initial State
		// Make sure we have a clean starting point with tracks and sections visible
		ensureTrackOpen( 0 );
		ensureSectionExpanded( 0, 0 );

		// Track Accordion Functionality
		// It should be open by default
		cy.get( '.nfd-track' ).first().should( 'have.attr', 'open' );
		
		// Close the track
		closeTrack( 0 );
		
		// It should close
		cy.get( '.nfd-track' ).first().should( 'not.have.attr', 'open' );
		
		// Open the track again
		openTrack( 0 );
		
		// It should open again
		cy.get( '.nfd-track' ).first().should( 'have.attr', 'open' );
		
		// Find a task with "new" status using robust helper
		getTaskByStatus( 'new' ).then( ( task ) => {
			// Complete the task
			completeTask( cy.wrap( task ) );
			
			// Check that a done task appeared (since DOM might rebuild)
			countTasksByStatus( 'done' ).should( 'be.greaterThan', 0 );
		} );

		// Task Dismissa
		
		// Get the initial count of new tasks
		let initialNewTaskCount;
		countTasksByStatus( 'new' ).then( ( count ) => {
			initialNewTaskCount = count;
		} );
		
		// Find a task with "new" status and dismiss it
		getTaskByStatus( 'new' ).then( ( task ) => {
			dismissTask( cy.wrap( task ) );
		} );
		
		// Verify task was dismissed by checking the count decreased
		countTasksByStatus( 'new' ).should( ( $count ) => {
			expect( $count ).to.be.lessThan( initialNewTaskCount );
		} );
		
	} );

	it( 'validates task links and button functionality', () => {
		// Ensure Proper Visibility First
		ensureTrackOpen( 0 );
		ensureSectionExpanded( 0, 0 );
		
		// Task Links and Buttons
		// Get a new task using robust helper
		getTaskByStatus( 'new' ).as( 'newTask' );
		
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

	// New test to verify version handling and merge functionality
	it( 'handles versioned data correctly', () => {
		// Ensure Proper Visibility First
		ensureTrackOpen( 0 );
		ensureSectionExpanded( 0, 0 );
		
		// Complete a task to create some user state
		getTaskByStatus( 'new' ).then( ( task ) => {
			completeTask( cy.wrap( task ) );
		} );
		
		// Verify the task is completed
		getTaskByStatus( 'done' ).should( 'have.length.greaterThan', 0 );
		
		// Refresh the page to trigger potential merge logic
		cy.reload();
		
		// Wait for app to reload
		waitForNextStepsApp();
		
		// Verify the completed task status is preserved after reload
		getTaskByStatus( 'done' ).should( 'have.length.greaterThan', 0 );
		
		// Verify the app still functions normally
		cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
		cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
		cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );
	} );
} );
