// <reference types="Cypress" />
import { 
	wpLogin,
	setTestNextStepsData,
	resetNextStepsData
} from '../wp-module-support/utils.cy';

describe( 'Next Steps Portal in Plugin App', { testIsolation: true }, () => {

	after( () => {
		// Reset test data
		resetNextStepsData();
	} );

	beforeEach( () => {
		wpLogin();
		// Set test Next Steps data
		setTestNextStepsData();
		cy.visit(
			'/wp-admin/admin.php?page=' + Cypress.env( 'pluginId' ) + '#/home'
		);
		cy.reload();

		// Intercept the task status update API call
		cy.intercept(
			{
				method: 'POST',
				url: /newfold-next-steps(\/|%2F)v1(\/|%2F)steps(\/|%2F)status/,
			},
			{
				statusCode: 200,
				body: true
			}
		).as( 'taskStatus' );
		cy.intercept(
			{
				method: 'POST',
				url: /newfold-next-steps(\/|%2F)v1(\/|%2F)steps(\/|%2F)section(\/|%2F)update/,
			},
			{
				statusCode: 200,
				body: {
					id: 'section1',
					open: false
				}
			}
		).as( 'sectionUpdate' );
	} );

	it( 'portal renders and displays correctly', () => {
		// Portal App Renders
		cy.get('#next-steps-portal').scrollIntoView().should('be.visible');
		cy.get( '.next-steps-fill #nfd-nextsteps' ).should( 'be.visible' );

		// Check Basic Structure
		cy.get( '.nfd-track' ).should( 'have.length', 2 );
		cy.get( '.nfd-section' ).should( 'have.length', 4 );
		cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length', 9 );

		// Check that the app has loaded with content
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' ).and( 'contain', 'This is a test plan' );

		// Marking a task complete updates task and progress bars
		// Find progress bar in first section
		cy.get('.nfd-section[data-nfd-section-id="section1"]').as( 'firstSection' );
		// Should have a progress bar
		cy.get( '@firstSection' ).find('.nfd-progress-bar').should('exist');
		
		// Validate initial progress values
		cy.get( '@firstSection' ).find('.nfd-progress-bar-label').should('have.text', '0/1');
		cy.get( '@firstSection' ).find('.nfd-progress-bar-inner').should('have.attr', 'data-percent', '0');

		// Task should be in new state
		cy.get( '@firstSection' ).find('#s1task1').should('have.attr', 'data-nfd-task-status', 'new');
		cy.get( '@firstSection' ).should('have.attr', 'open');
		// Complete task
		cy.get( '#s1task1.nfd-nextsteps-step-container .nfd-nextsteps-step-new .nfd-nextsteps-button-todo' )
			.click();
		// Wait for API call
		cy.wait('@taskStatus');

		// Task should now be in done state
		cy.get( '@firstSection' ).find('#s1task1').should('have.attr', 'data-nfd-task-status', 'done');

		// Progress should update
		cy.get( '@firstSection' ).find('.nfd-progress-bar-label').should('have.text', '1/1');
		cy.get( '@firstSection' ).find('.nfd-progress-bar-inner').should('have.attr', 'data-percent', '100');
				
		// Celebrate should be visible
		cy.get( '@firstSection' ).find('.nfd-section-celebrate').should('be.visible');
		cy.get( '@firstSection' ).find('.nfd-section-celebrate-text').should('have.text', 'All complete!');
		cy.get( '@firstSection' ).find('.nfd-nextsteps-section-close-button').should('be.visible');

	} );
} );
