// <reference types="Cypress" />
import { 
	wpLogin,
	setTestNextStepsData,
	resetNextStepsData
} from '../wp-module-support/utils.cy';
import { setupNextStepsIntercepts } from '../wp-module-support/api-intercepts.cy';

describe('Next Steps Widget', { testIsolation: true }, () => {

	after( () => {
		// Reset test data
		resetNextStepsData();
	} );

	beforeEach(() => {
		// Set test Next Steps data
		setTestNextStepsData();
		wpLogin();
		cy.visit('/wp-admin/index.php');
		
		// Wait for widget to be visible
		cy.get('#nfd_next_steps_widget').should('be.visible');
		cy.get('#nfd-nextsteps').should('be.visible');
		
		// Wait for React app to load by checking for tracks
		cy.get('.nfd-track', { timeout: 10000 }).should('exist');

		// Set up all Next Steps API intercepts
		setupNextStepsIntercepts();
	});

	it('renders the widget structure correctly', () => {
		// Widget container
		cy.get('#nfd_next_steps_widget')
			.should('be.visible')
			.within(() => {
				cy.get('h2').should('contain', 'Next Steps');
			});

		// Main app structure
		cy.get('#nfd-nextsteps')
			.should('be.visible')
			.should('have.attr', 'data-nfd-plan-id');

		// Should have tracks
		cy.get('.nfd-track').should('have.length', 2);
		
		// First track should be open by default
		cy.get('.nfd-track').first().should('have.attr', 'open');

		// Track has a section
		cy.get('.nfd-track').first().within(() => {
			cy.get('.nfd-section').should('have.length', 3);
			
			// Check section structure
			cy.get('.nfd-section').first().within(() => {
				cy.get('.nfd-section-header').should('exist');
				cy.get('.nfd-section-title').should('exist');

				cy.get('.nfd-nextsteps-step-container').should('have.length', 1);
				cy.get('.nfd-nextsteps-step-container').first().should('have.attr', 'id').and('contain', 's1task1');
				// Task should have proper data attributes
				cy.get('.nfd-nextsteps-step-container').first().should('have.attr', 'data-test-id').and('contain', 'test-task-1');
				cy.get('.nfd-nextsteps-step-container').first().should('have.attr', 'data-nfd-id').and('contain', 'test-task-1');
			});
		});

		// Section 2 should have new dismissed and completed task.
		cy.get('.nfd-section[data-nfd-section-id="section2"]').as( 'secondSection' );
		cy.get( '@secondSection' ).find('.nfd-progress-bar').should('exist');
		cy.get( '@secondSection' ).find('.nfd-progress-bar-label').should('have.text', '1/2');
		cy.get( '@secondSection' ).find('.nfd-progress-bar-inner').should('have.attr', 'data-percent', '50');

		// Section 2 should have 2 tasks
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-container').should('have.length', 3);
		// A single new task should be visible
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-container').first().should('have.attr', 'data-nfd-task-status', 'new');
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-new').scrollIntoView().should('have.length', 1);
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-new').parent().should('have.attr', 'id').and('contain', 's2task1');
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-new').as( 's2t1' );
		cy.get( '@s2t1' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-todo').should('be.visible');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-dismiss').should('exist');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-link').should('be.visible');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-link').should('have.attr', 'href').and('contain', 'bluehost.com');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-link').should('have.attr', 'data-nfd-click').and('contain', 'nextsteps_step_link');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-link').should('have.attr', 'data-nfd-event-category').and('contain', 'nextsteps_step');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-link').should('have.attr', 'data-nfd-event-key').and('contain', 's2task1');
		// Content should be visible
		cy.get( '@s2t1' ).find('.nfd-nextsteps-step-content').should('contain', 'New Task');
		// Content should contain a link
		cy.get( '@s2t1' ).find('.nfd-nextsteps-step-content').find('a').should('be.visible');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-step-content').find('a').should('have.attr', 'href').and('contain', 'bluehost.com');
		cy.get( '@s2t1' ).find('.nfd-nextsteps-step-content').find('a').should('have.attr', 'target').and('contain', '_blank');
		// A single dismissed task should be visible
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-container').eq(1).should('have.attr', 'data-nfd-task-status', 'dismissed');
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-dismissed').as( 's2t2' );
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-dismissed').should('have.length', 1);
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-dismissed').parent().should('have.attr', 'id').and('contain', 's2task2');
		cy.get( '@s2t2' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-redo').should('be.visible');
		cy.get( '@s2t2' ).find('.nfd-nextsteps-button.nfd-nextsteps-button-dismiss').should('exist');
		// A single done task should be visible
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-container').last().should('have.attr', 'data-nfd-task-status', 'done');
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-done').should('have.length', 1);
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-done').parent().should('have.attr', 'id').and('contain', 's2task3');
		cy.get( '@secondSection' ).find('.nfd-nextsteps-step-done').find('.nfd-nextsteps-button.nfd-nextsteps-button-redo').should('be.visible');

		// Section 3 should have 2 complete tasks
		cy.get('.nfd-section[data-nfd-section-id="section3"]').as( 'thirdSection' );
		// Section 3 should have progress bar with 2/2
		cy.get( '@thirdSection' ).find('.nfd-progress-bar').scrollIntoView().should('exist');
		cy.get( '@thirdSection' ).find('span.nfd-progress-bar-label').should('have.text', '2/2');
		cy.get( '@thirdSection' ).find('.nfd-progress-bar-inner').should('have.attr', 'data-percent', '100');
		// Section 3 should have 2 complete tasks
		cy.get( '@thirdSection' ).find('.nfd-nextsteps-step-container').should('have.length', 2);
		cy.get( '@thirdSection' ).find('.nfd-nextsteps-step-container').first().should('have.attr', 'data-nfd-task-status', 'done');
		cy.get( '@thirdSection' ).find('.nfd-nextsteps-step-container').last().should('have.attr', 'data-nfd-task-status', 'done');

		// Track 2 should have 1 section with 3 new tasks
		cy.get('.nfd-section[data-nfd-section-id="section4"]').as( 'fourthSection' );
		// Track 2 should have progress bar with 0/3
		cy.get( '@fourthSection' ).find('.nfd-progress-bar').scrollIntoView().should('exist');
		cy.get( '@fourthSection' ).find('span.nfd-progress-bar-label').should('have.text', '0/3');
		cy.get( '@fourthSection' ).find('.nfd-progress-bar-inner').should('have.attr', 'data-percent', '0');
		// Section 4 should have 3 new tasks
		cy.get( '@fourthSection' ).find('.nfd-nextsteps-step-container').should('have.length', 3);
		cy.get( '@fourthSection' ).find('.nfd-nextsteps-step-container').first().should('have.attr', 'data-nfd-task-status', 'new');
		cy.get( '@fourthSection' ).find('.nfd-nextsteps-step-container').last().should('have.attr', 'data-nfd-task-status', 'new');
	});

	it('marking a task complete updates task and progress bars', () => {
		// Find progress bar in first section
		cy.get('.nfd-section[data-nfd-section-id="section1"]').as( 'firstSection' );
		// Should have a progress bar
		cy.get( '@firstSection' ).find('.nfd-progress-bar').should('exist');
		
		// Validate initial progress values
		cy.get( '@firstSection' ).find('.nfd-progress-bar-label').should('have.text', '0/1');
		cy.get( '@firstSection' ).find('.nfd-progress-bar-inner').should('have.attr', 'data-percent', '0');

		// Task should be in new state
		cy.get( '@firstSection' ).find('#s1task1').should('have.attr', 'data-nfd-task-status', 'new');

		// Complete task
		cy.get( '@firstSection' ).find('#s1task1.nfd-nextsteps-step-container .nfd-nextsteps-step-new .nfd-nextsteps-button-todo')
			.click();
		// Wait for API call
		cy.wait('@taskEndpoint');

		// Task should now be in done state
		cy.get( '@firstSection' ).find('#s1task1').should('have.attr', 'data-nfd-task-status', 'done');

		// Progress should update
		cy.get( '@firstSection' ).find('.nfd-progress-bar-label').should('have.text', '1/1');
		cy.get( '@firstSection' ).find('.nfd-progress-bar-inner').should('have.attr', 'data-percent', '100');
				
		// Celebrate should be visible
		cy.get( '@firstSection' ).find('.nfd-section-celebrate').should('be.visible');
		cy.get( '@firstSection' ).find('.nfd-section-celebrate-text').should('have.text', 'All complete!');
		cy.get( '@firstSection' ).find('.nfd-nextsteps-section-close-button').should('be.visible');

		// Close celebration closes section
		cy.get( '@firstSection' ).should('have.attr', 'open');
		cy.get( '@firstSection' ).find('.nfd-section-complete')
			.click();
		cy.wait( '@sectionEndpoint' );
		cy.get( '@firstSection' ).find('.nfd-section-complete').should('not.be.visible');
		cy.get( '@firstSection' ).find('.nfd-nextsteps-step-container').should('not.be.visible');
		cy.get( '@firstSection' ).should('not.have.attr', 'open');
		// Open the section
		cy.get( '@firstSection' ).find('.nfd-section-header')
			.click();
		cy.get( '@firstSection' ).should('have.attr', 'open');
	});

	it('dismisses a task and verifies state change', () => {
		// Find and dismiss a task
		cy.get( '.nfd-nextsteps-step-container[data-nfd-task-status="new"]' ).first().as( 'firstNewTask' );
		cy.get( '@firstNewTask' ).should('have.attr', 'id', 's1task1');
		cy.get( '@firstNewTask' ).find('.nfd-nextsteps-button-dismiss').should('exist');
		cy.get( '@firstNewTask' ).find('.nfd-nextsteps-button-dismiss').should('not.be.visible');
		// Click dismiss button - force due to cypress not being able to trigger hover state
		cy.get( '@firstNewTask' ).find('.nfd-nextsteps-button-dismiss')
			.click( { force: true } );		
		// Wait for API call
		cy.wait( '@taskEndpoint' );
		// Task should now be dismissed
		cy.get( '#s1task1' ).should('have.attr', 'data-nfd-task-status', 'dismissed');
	});

	it('handles track and section toggle functionality', () => {
		// First track should be open by default
		cy.get('.nfd-track').first().should('have.attr', 'open');
		// Close the track
		cy.get('.nfd-track').first().find('.nfd-track-header')
			.click();
		cy.wait('@trackEndpoint');
		// Should be closed
		cy.get('.nfd-track').first().should('not.have.attr', 'open');
		// Open the track again
		cy.get('.nfd-track').first().find('.nfd-track-header')
			.click();
		cy.wait('@trackEndpoint');
		// Should be open
		cy.get('.nfd-track').first().should('have.attr', 'open');

		// Get first section and test toggle
		cy.get('.nfd-section').first().then($section => {
			const isOpen = $section.attr('open');
			
			// Click section header to toggle
			cy.wrap($section).find('.nfd-section-header')
				.click();
			cy.wait('@sectionEndpoint');
			
			// State should change
			if (isOpen) {
				cy.wrap($section).should('not.have.attr', 'open');
			} else {
				cy.wrap($section).should('have.attr', 'open');
			}
		});
	});
});