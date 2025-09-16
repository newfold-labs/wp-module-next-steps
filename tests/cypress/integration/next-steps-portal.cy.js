// <reference types="Cypress" />
import { 
	wpLogin,
	setTestNextStepsData,
	resetNextStepsData
} from '../wp-module-support/utils.cy';
import { setupNextStepsIntercepts } from '../wp-module-support/api-intercepts.cy';

describe( 'Next Steps Portal in Plugin App', { testIsolation: true }, () => {

	beforeEach( () => {
		wpLogin();
		// Set test Next Steps data
		setTestNextStepsData();
		// Set up all Next Steps API intercepts
		setupNextStepsIntercepts();
		// Visit the Next Steps portal
		cy.visit(
			'/wp-admin/admin.php?page=' + Cypress.env( 'pluginId' ) + '#/home'
		);
		// Reload the page to ensure the intercepts are working and updated test content is loaded
		cy.reload();

		// Portal App Renders
		cy.get( '#next-steps-portal' ).scrollIntoView().should( 'exist' );
		cy.get( '.next-steps-fill #nfd-nextsteps', { timeout: 25000 } ).should( 'be.visible' );
	} );

	after( () => {
		// Reset test data
		resetNextStepsData();
	} );

	it( 'portal renders and displays correctly', () => {

		// Check Basic Structure
		cy.get( '.nfd-track' ).should( 'have.length', 2 );
		cy.get( '.nfd-section' ).should( 'have.length', 4 );
		cy.get( '.nfd-nextsteps-task-container' ).should( 'have.length', 9 );

		// Check that the app has loaded with content
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' ).and( 'contain', 'This is a test plan' );

		// Marking a task complete updates task and progress bars
		// Progress bar in first section
		cy.get('[data-nfd-section-id="section1"] .nfd-progress-bar').should('exist');
		
		// Validate initial progress values
		cy.get('[data-nfd-section-id="section1"] .nfd-progress-bar-label').should('have.text', '0/1');
		cy.get('[data-nfd-section-id="section1"] .nfd-progress-bar-inner').should('have.attr', 'data-percent', '0');

		// Task should be in new state
		cy.get('[data-nfd-section-id="section1"] #task-s1task1').should('have.attr', 'data-nfd-task-status', 'new');
		cy.get('[data-nfd-section-id="section1"]').should('have.attr', 'open');
		// Complete task
		cy.get( '#task-s1task1 .nfd-nextsteps-task-new .nfd-nextsteps-button-todo' )
			.scrollIntoView().click();
		// Wait for API calls
		cy.wait( '@taskEndpoint' ).then( (interception) => {
			cy.log( '@taskEndpoint response:' + JSON.stringify(interception.response.body) );
		} );
		cy.wait( '@sectionEndpoint' ).then( (interception) => {
			cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
		} );
		// wait for task to update and celebration to load
		cy.wait( 250 );
		// Task should now be in done state
		cy.get('[data-nfd-task-id="s1task1"]').should('have.attr', 'data-nfd-task-status', 'done');

		// Progress should update
		cy.get('.nfd-progress-bar-label').first().should('have.text', '1/1');
		cy.get('.nfd-progress-bar-inner').first().should('have.attr', 'data-percent', '100');
				
		// Celebrate should be visible
		cy.get('.nfd-section-complete').first().should('be.visible');
		cy.get('.nfd-section-celebrate-text').first().should('have.text', 'All complete!');
		cy.get('.nfd-nextsteps-section-close-button').first().should('be.visible');

		// Close celebration closes section
		cy.get('[data-nfd-section-id="section1"]').should('have.attr', 'open');
		cy.get('.nfd-nextsteps-section-close-button').first()
			.click();
		cy.wait( '@sectionEndpoint' ).then( (interception) => {
			cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
		} );
		cy.get('[data-nfd-section-id="section1"] .nfd-section-celebrate').should('not.be.visible');
		cy.get('[data-nfd-section-id="section1"] .nfd-nextsteps-task-container').should('not.be.visible');
		cy.get('[data-nfd-section-id="section1"]').should('not.have.attr', 'open');
		// Open the section
		cy.get('[data-nfd-section-id="section1"] .nfd-section-header')
			.click();
		cy.wait( '@sectionEndpoint' ).then( (interception) => {
			cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
		} );
		cy.get('[data-nfd-section-id="section1"]').should('have.attr', 'open');
	} );
} );
