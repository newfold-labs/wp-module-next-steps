// <reference types="Cypress" />
import { 
	wpLogin,
	setTestNextStepsData,
	resetNextStepsData
} from '../wp-module-support/utils.cy';
import { setupNextStepsIntercepts } from '../wp-module-support/api-intercepts.cy';

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

		// Set up all Next Steps API intercepts
		setupNextStepsIntercepts();
	} );

	it( 'portal renders and displays correctly', () => {
		// Portal App Renders
		cy.get( '#next-steps-portal' ).scrollIntoView().should('be.visible');
		cy.get( '.next-steps-fill #nfd-nextsteps' ).should( 'be.visible' );

		// Check Basic Structure
		cy.get( '.nfd-track' ).should( 'have.length', 2 );
		cy.get( '.nfd-section' ).should( 'have.length', 4 );
		cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length', 9 );

		// Check that the app has loaded with content
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' ).and( 'contain', 'This is a test plan' );

		// Marking a task complete updates task and progress bars
		// Progress bar in first section
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-progress-bar').should('exist');
		
		// Validate initial progress values
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-progress-bar-label').should('have.text', '0/1');
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-progress-bar-inner').should('have.attr', 'data-percent', '0');

		// Task should be in new state
		cy.get('.nfd-section[data-nfd-section-id="section1"] #task-s1task1').should('have.attr', 'data-nfd-task-status', 'new');
		cy.get('.nfd-section[data-nfd-section-id="section1"]').should('have.attr', 'open');
		// Complete task
		cy.get( '#task-s1task1.nfd-nextsteps-step-container .nfd-nextsteps-step-new .nfd-nextsteps-button-todo' )
			.click();
		// Wait for API call
		cy.wait( '@taskEndpoint' ).then( (interception) => {
            cy.log( '@taskEndpoint response:' + JSON.stringify(interception.response.body) );
        } );

		// Task should now be in done state
		cy.get('.nfd-section[data-nfd-section-id="section1"] #task-s1task1').should('have.attr', 'data-nfd-task-status', 'done');

		// Progress should update
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-progress-bar-label').should('have.text', '1/1');
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-progress-bar-inner').should('have.attr', 'data-percent', '100');
				
		// Celebrate should be visible
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-section-celebrate').should('be.visible');
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-section-celebrate-text').should('have.text', 'All complete!');
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-nextsteps-section-close-button').should('be.visible');

		// Close celebration closes section
		cy.get('.nfd-section[data-nfd-section-id="section1"]').should('have.attr', 'open');
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-section-complete')
			.click();
		cy.wait( '@sectionEndpoint' ).then( (interception) => {
            cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
        } );
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-section-complete').should('not.be.visible');
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-nextsteps-step-container').should('not.be.visible');
		cy.get('.nfd-section[data-nfd-section-id="section1"]').should('not.have.attr', 'open');
		// Open the section
		cy.get('.nfd-section[data-nfd-section-id="section1"] .nfd-section-header')
			.click();
		cy.wait( '@sectionEndpoint' ).then( (interception) => {
            cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
        } );
		cy.get('.nfd-section[data-nfd-section-id="section1"]').should('have.attr', 'open');
	} );
} );
