// <reference types="Cypress" />
import { wpLogin, wpCli } from '../wp-module-support/utils.cy';
import {
	waitForNextStepsApp,
	getTaskByStatus,
	verifyTaskDataAttributes,
	verifyTaskLinks,
	verifyTaskIcons,
	toggleSection,
	toggleDismissedTasks
} from '../wp-module-support/next-steps-helpers.cy';

describe( 'Next Steps Portal in Plugin App', { testIsolation: true }, () => {
	before( () => {
		// Reset Next Steps data to ensure clean state for tests
		wpCli( 'option delete nfd_next_steps', false );
	} );
	beforeEach( () => {
		wpLogin();
		
		cy.visit(
			'/wp-admin/admin.php?page=' + Cypress.env( 'pluginId' ) + '#/home'
		);
	} );

	it( 'renders portal structure and displays progress bars correctly', () => {
		// === Portal App Rendering ===
		cy.get( '#next-steps-slot .next-steps-fill #nfd-nextsteps' )
			.scrollIntoView()
			.should( 'be.visible' );

		// === Basic Structure ===
		waitForNextStepsApp();
		
		// Check that the app has loaded with content
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' );

		// === Progress Bars Display ===
		// Check if progress bars exist anywhere in the app
		cy.get( '#nfd-nextsteps' ).then( ( $app ) => {
			// Check if progress bars exist, but don't fail if they don't
			cy.wrap( $app ).find( '.nfd-progress-bar' ).should( 'exist' );
		} );
	} );

	it( 'handles all portal interactions and functionality correctly', () => {
		// === Basic Interactions ===
		// Test that tracks can be toggled
		cy.get( '.nfd-track' ).first().should( 'have.attr', 'open' );
		
		// Test that sections can be toggled
		toggleSection( 0, 0 );
		
		// Test that task buttons exist and are clickable
		getTaskByStatus( 'new' ).first().then( ( task ) => {
			verifyTaskIcons( cy.wrap( task ), 'new' );
		} );

		// === Show/Hide Dismissed Tasks ===
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'be.visible' );
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'contain.text', 'View skipped tasks' );
		
		// Test that the button is clickable
		toggleDismissedTasks();
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'contain.text', 'Hide skipped tasks' );

		// === Task Links and Navigation ===
		getTaskByStatus( 'new' ).first().then( ( task ) => {
			verifyTaskLinks( cy.wrap( task ) );
			verifyTaskDataAttributes( cy.wrap( task ) );
		} );
	} );
} );
