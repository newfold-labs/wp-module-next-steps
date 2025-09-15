// <reference types="Cypress" />
import { 
	wpLogin,
	setTestCardsNextStepsData,
	resetNextStepsData
} from '../wp-module-support/utils.cy';
import { setupNextStepsIntercepts } from '../wp-module-support/api-intercepts.cy';

describe( 'Next Steps Portal in Plugin App with Cards', { testIsolation: true }, () => {

	beforeEach( () => {
		wpLogin();
		// Set test Next Steps data
		setTestCardsNextStepsData();
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
		cy.get( '.next-steps-fill #nfd-nextsteps', { timeout: 10000 } ).should( 'be.visible' );
	} );

	after( () => {
		// Reset test data
		resetNextStepsData();
	} );

	it( 'portal renders and displays correctly', () => {

        // Check for 3 total sections
        cy.get( '.nfd-nextsteps-section-card' ).should( 'have.length', 3 );
        // Check that expired section is not rendered
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="section-expired"]' ).should( 'not.exist' );

        // Check that section 1 is rendered with correct title, description, cta, icon, modal title, modal description
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"]' ).scrollIntoView().should( 'be.visible' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-section-card-title' ).should( 'have.text', 'Test Section 1' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-section-card-description' ).should( 'have.text', 'Section 1 with 1 task.' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-buttons .nfd-button' ).should( 'have.text', 'CTA 1 Text' );
        // first incomplete section has primary button
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-buttons .nfd-button' ).should( 'have.class', 'nfd-button--primary' );
        // section with single task loads task href on section button
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-buttons .nfd-button' ).should( 'have.attr', 'href' )
        .then( ( href ) => {
            expect(
                href.includes( 'www.bluehost.com' )
            ).to.be.true;
        } );
        // check that svg images are properly loaded and visible
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-section-card-icon-wrapper svg' ).should( 'be.visible' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-section-card__wireframe svg' ).should( 'be.visible' );

        // check section 1 updates when skipped
        cy.get( '.nfd-nextstep-section-card__dismissed-badge' ).should( 'not.exist' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-button--skip' ).scrollIntoView().should( 'be.visible' );
        // CLICK skip section 1 button
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-button--skip' )
            .click();
        cy.wait( '@sectionEndpoint' ).then( (interception) => {
            cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
        } );
        cy.get( '.nfd-nextstep-section-card__dismissed-badge' ).scrollIntoView().should( 'be.visible' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"]' ).should( 'have.attr', 'data-nfd-section-status', 'dismissed' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-button--undo' ).should( 'be.visible' );
        // CLICK undo section 1 button
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-button--undo' )
            .click();
        cy.wait( '@sectionEndpoint' ).then( (interception) => {
            cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
        } );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextstep-section-card__dismissed-badge' ).should( 'not.exist' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"]' ).should( 'have.attr', 'data-nfd-section-status', 'new' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"] .nfd-nextsteps-button--skip' ).should( 'be.visible' );

        // check section 2 renders and task modal opens with proper tasks
        cy.get( '#section-card-section2 .nfd-nextsteps-buttons .nfd-button' ).should( 'not.have.attr', 'href' );
        cy.get( '#section-card-section2 .nfd-nextsteps-buttons .nfd-button' )
            .click();
        cy.get('.nfd-modal__layout').should( 'be.visible' );
        cy.get('.nfd-modal__layout').find( 'h1.nfd-title' ).should( 'have.text', 'Section 2 Modal Title' );
        cy.get('.nfd-modal__layout').find( 'p' ).should( 'have.text', 'Section 2 modal description.' );
        cy.get('.nfd-nextstep-tasks-modal__tasks').should( 'be.visible' );
        //task 1
        cy.get( '.nfd-nextsteps-task-container[data-nfd-task-id="s2task1"]' ).as( 's2task1' );
        cy.get( '@s2task1' ).should( 'be.visible' );
        cy.get( '@s2task1' ).should( 'have.attr', 'data-nfd-task-status', 'new' );
        cy.get( '@s2task1' ).find( '.nfd-title' ).should( 'have.text', 'New Task' );
        //task 2
        cy.get( '.nfd-nextsteps-task-container[data-nfd-task-id="s2task2"]' ).as( 's2task2' );
        cy.get( '@s2task2' ).should( 'be.visible' );
        cy.get( '@s2task2' ).should( 'have.attr', 'data-nfd-task-status', 'dismissed' );
        cy.get( '@s2task2' ).find( '.nfd-title' ).should( 'have.text', 'Dismissed Task' );
        //task 3
        cy.get( '.nfd-nextsteps-task-container[data-nfd-task-id="s2task3"]' ).as( 's2task3' );
        cy.get( '@s2task3' ).should( 'be.visible' );
        cy.get( '@s2task3' ).should( 'have.attr', 'data-nfd-task-status', 'done' );
        cy.get( '@s2task3' ).find( '.nfd-title' ).should( 'have.text', 'Completed Task' );
        // check section 2 modal tasks marked as done updates section card as done
        cy.get( '@s2task1' ).find( '.nfd-nextsteps-button-todo' ).should( 'be.visible' );
        cy.get( '@s2task1' ).find( '.nfd-nextsteps-button-todo' )
            .click();
        cy.wait( '@taskEndpoint' ).then( (interception) => {
            cy.log( '@taskEndpoint response:' + JSON.stringify(interception.response.body) );
        } );
        cy.wait( '@sectionEndpoint' ).then( (interception) => {
            cy.log( '@sectionEndpoint response:' + JSON.stringify(interception.response.body) );
        } );
        cy.get( '.nfd-modal__layout' ).should( 'not.exist' );
        cy.get( '.nfd-nextstep-tasks-modal__tasks' ).should( 'not.exist' );
        // check section 2 card is updated to done
        cy.get( '#section-card-section2' ).scrollIntoView().should( 'be.visible' );
        cy.get( '#section-card-section2 .nfd-nextstep-section-card__completed-badge' ).should( 'be.visible' );
        cy.get( '#section-card-section2' ).should( 'have.attr', 'data-nfd-section-status', 'done' );


        // section 3 
        cy.get( '#section-card-section3' ).scrollIntoView().should( 'be.visible' );
        // has secondary button
        cy.get( '#section-card-section3 .nfd-nextsteps-buttons .nfd-button' ).should( 'have.class', 'nfd-button--secondary' );

        // Check that completed section 3 is rendered with complete badge
        cy.get( '#section-card-section3' ).should( 'have.attr', 'data-nfd-section-status', 'done' );
        cy.get( '#section-card-section3' ).should( 'have.attr', 'data-nfd-date-completed' );
        cy.get( '#section-card-section3' ).should( 'have.attr', 'data-nfd-now-date' );
        cy.get( '#section-card-section3' ).should( 'have.attr', 'data-nfd-expiry-date' );
        cy.get( '#section-card-section3' ).should( 'have.attr', 'data-nfd-expires-in', 'a day from now' );
        cy.get( '#section-card-section3 .nfd-nextstep-section-card__completed-badge' ).scrollIntoView().should( 'be.visible' );
	} );
} );
