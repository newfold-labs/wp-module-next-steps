// <reference types="Cypress" />
import { wpLogin, resetNextStepsData } from '../wp-module-support/utils.cy';
import {
	waitForNextStepsApp,
	getTaskByStatus,
	verifyTaskDataAttributes,
	verifyTaskLinks,
	verifyTaskIcons,
	toggleSection
} from '../wp-module-support/next-steps-helpers.cy';

describe( 'Next Steps Portal in Plugin App', { testIsolation: true }, () => {
	before( () => {
		// Reset Next Steps data to ensure clean state for tests
		resetNextStepsData();
	} );
		
	beforeEach( () => {
		wpLogin();
		cy.visit(
			'/wp-admin/admin.php?page=' + Cypress.env( 'pluginId' ) + '#/home'
		);
	} );

	it( 'renders portal structure and displays progress bars correctly', () => {
		// === Portal App Rendering ===
		cy.get( '.next-steps-fill #nfd-nextsteps' )
			.scrollIntoView()
			.should( 'be.visible' );

		// === Basic Structure ===
		waitForNextStepsApp();

		// Check that the app has loaded with content
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' );

		// === Progress Bars Display ===
		cy.get( '.nfd-progress-bar' ).should( 'exist' );
	} );

	it( 'handles all portal interactions and functionality correctly', () => {
		// Test that sections can be toggled
		toggleSection( 0, 0 );
		// Test that the first track is still open
		cy.get( '.nfd-track' ).first().should( 'have.attr', 'open' );

		// Test that task buttons exist and are clickable
		getTaskByStatus( 'new' ).first().then( ( task ) => {
			verifyTaskIcons( cy.wrap( task ), 'new' );
		} );

		// === Task Links and Navigation ===
		getTaskByStatus( 'new' ).first().then( ( task ) => {
			verifyTaskLinks( cy.wrap( task ) );
			verifyTaskDataAttributes( cy.wrap( task ) );
		} );

		// Verify the app rendered correctly
		getTaskByStatus( 'new' ).should( 'have.length.greaterThan', 0 );
		cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
		cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
	} );
} );
