// <reference types="Cypress" />
import { 
	wpLogin,
	setTestCardsNextStepsData,
	resetNextStepsData
} from '../wp-module-support/utils.cy';
import { setupNextStepsIntercepts } from '../wp-module-support/api-intercepts.cy';

describe( 'Next Steps Portal in Plugin App with Cards', { testIsolation: true }, () => {

	after( () => {
		// Reset test data
		resetNextStepsData();
	} );

	beforeEach( () => {
		wpLogin();
		// Set test Next Steps data
		setTestCardsNextStepsData();
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

        // Check for 3 total sections
        cy.get( '.nfd-nextsteps-section-card' ).should( 'have.length', 3 );

        // Check that section 1 is rendered with correct title, description, cta, icon, modal title, modal description
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="customize_your_store"]' ).as( 'section1Card' );
        cy.get( '@section1Card' ).scrollIntoView().should( 'be.visible' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-section-card-title' ).should( 'have.text', 'Test Section 1' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-section-card-description' ).should( 'have.text', 'Section 1 with 1 task.' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-buttons .nfd-button' ).should( 'have.text', 'CTA 1 Text' );
        // first incomplete section has primary button
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-buttons .nfd-button' ).should( 'have.class', 'nfd-button--primary' );
        // section with single task loads task href on section button
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-buttons .nfd-button' ).should( 'have.attr', 'href' )
        .then( ( href ) => {
            expect(
                href.includes( 'www.bluehost.com' )
            ).to.be.true;
        } );
        // check that svg images are properly loaded and visible
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-section-card-icon-wrapper svg' ).should( 'be.visible' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-section-card__wireframe svg' ).should( 'be.visible' );
        
        // following sections have secondary button
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="section2"]' ).as( 'section2Card' );
        cy.get( '@section2Card' ).scrollIntoView().should( 'be.visible' );
        cy.get( '@section2Card' ).find( '.nfd-nextsteps-buttons .nfd-button' ).should( 'have.class', 'nfd-button--secondary' );
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="section3"]' ).as( 'section3Card' );
        cy.get( '@section3Card' ).scrollIntoView().should( 'be.visible' );
        cy.get( '@section3Card' ).find( '.nfd-nextsteps-buttons .nfd-button' ).should( 'have.class', 'nfd-button--secondary' );

        // section 2 has no icon or wireframes
        cy.get( '@section2Card' ).find( '.nfd-nextsteps-section-card-icon-wrapper svg' ).should( 'not.exist' );
        cy.get( '@section2Card' ).find( '.nfd-nextsteps-section-card__wireframe svg' ).should( 'not.exist' );
        cy.get( '@section2Card' ).should( 'have.attr', 'data-nfd-section-status', 'new' );
        // check section 2 renders and task modal opens with proper tasks
        cy.get( '@section2Card' ).find( '.nfd-nextsteps-buttons .nfd-button' ).should( 'not.have.attr', 'href' );
        cy.get( '@section2Card' ).find( '.nfd-nextsteps-buttons .nfd-button' )
            .click();
        cy.get('.nfd-modal__layout').should( 'be.visible' );
        cy.get('.nfd-modal__layout').find( 'h1.nfd-title' ).should( 'have.text', 'Section 2 Modal Title' );
        cy.get('.nfd-modal__layout').find( 'p' ).should( 'have.text', 'Section 2 modal description.' );
        cy.get('.nfd-nextstep-tasks-modal__tasks').should( 'be.visible' );
        //task 1
        cy.get( '.nfd-nextsteps-step-container[data-nfd-task-id="s2task1"]' ).as( 's2task1' );
        cy.get( '@s2task1' ).should( 'be.visible' );
        cy.get( '@s2task1' ).should( 'have.attr', 'data-nfd-task-status', 'new' );
        cy.get( '@s2task1' ).find( '.nfd-title' ).should( 'have.text', 'New Task' );
        //task 2
        cy.get( '.nfd-nextsteps-step-container[data-nfd-task-id="s2task2"]' ).as( 's2task2' );
        cy.get( '@s2task2' ).should( 'be.visible' );
        cy.get( '@s2task2' ).should( 'have.attr', 'data-nfd-task-status', 'dismissed' );
        cy.get( '@s2task2' ).find( '.nfd-title' ).should( 'have.text', 'Dismissed Task' );
        //task 3
        cy.get( '.nfd-nextsteps-step-container[data-nfd-task-id="s2task3"]' ).as( 's2task3' );
        cy.get( '@s2task3' ).should( 'be.visible' );
        cy.get( '@s2task3' ).should( 'have.attr', 'data-nfd-task-status', 'done' );
        cy.get( '@s2task3' ).find( '.nfd-title' ).should( 'have.text', 'Completed Task' );
        // check section 2 modal tasks marked as done updates section card as done
        cy.get( '@s2task1' ).find( '.nfd-nextsteps-button-todo' ).should( 'be.visible' );
        cy.get( '@s2task1' ).find( '.nfd-nextsteps-button-todo' )
            .click();
        cy.wait( '@taskEndpoint' );
        cy.wait( '@sectionEndpoint' );
        cy.get( '.nfd-modal__layout' ).should( 'not.exist' );
        cy.get( '.nfd-nextstep-tasks-modal__tasks' ).should( 'not.exist' );
        // check section 2 card is updated to done
        // cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="section2"]' ).as( 'section2Card2' );
        // cy.get( '@section2Card2' ).scrollIntoView().should( 'be.visible' );
        // cy.get( '@section2Card2' ).find( '.nfd-nextstep-section-card__completed-badge' ).should( 'be.visible' );
        // cy.get( '@section2Card2' ).should( 'have.attr', 'data-nfd-section-status', 'done' );

        // Check that expired section is not rendered
        cy.get( '.nfd-nextsteps-section-card[data-nfd-section-id="section-expired"]' ).should( 'not.exist' );

        // Check that completed section 3 is rendered with complete badge
        cy.get( '@section3Card' ).scrollIntoView().should( 'be.visible' );
        cy.get( '@section3Card' ).find( '.nfd-nextstep-section-card__completed-badge' ).should( 'be.visible' );
        cy.get( '@section3Card' ).should( 'have.attr', 'data-nfd-section-status', 'done' );
        cy.get( '@section3Card' ).should( 'have.attr', 'data-nfd-date-completed' );
        cy.get( '@section3Card' ).should( 'have.attr', 'data-nfd-now-date' );
        cy.get( '@section3Card' ).should( 'have.attr', 'data-nfd-expiry-date' );
        cy.get( '@section3Card' ).should( 'have.attr', 'data-nfd-expires-in', 'a day from now' );

        // check section 1 updates when skipped
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-button--skip' ).should( 'be.visible' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-button--skip' )
            .click();
        cy.wait( '@sectionEndpoint' );
        cy.get( '@section1Card' ).find( '.nfd-nextstep-section-card__dismissed-badge' ).should( 'be.visible' );
        cy.get( '@section1Card' ).should( 'have.attr', 'data-nfd-section-status', 'dismissed' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-button--undo' ).should( 'be.visible' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-button--undo' )
            .click();
        cy.wait( '@sectionEndpoint' );
        cy.get( '@section1Card' ).find( '.nfd-nextstep-section-card__dismissed-badge' ).should( 'not.exist' );
        cy.get( '@section1Card' ).should( 'have.attr', 'data-nfd-section-status', 'new' );
        cy.get( '@section1Card' ).find( '.nfd-nextsteps-button--skip' ).should( 'be.visible' );
	} );
} );
