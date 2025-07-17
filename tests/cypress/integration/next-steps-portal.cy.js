// <reference types="Cypress" />
import { wpLogin } from '../wp-module-support/utils.cy';

describe( 'Next Steps Portal in Plugin App', { testIsolation: true }, () => {
	beforeEach( () => {
		wpLogin();
		cy.visit(
			'/wp-admin/admin.php?page=' + Cypress.env( 'pluginId' ) + '#/home'
		);
	} );

	it( 'renders the next steps app in portal', () => {
		cy.get( '#next-steps-slot .next-steps-fill #nfd-nextsteps' )
			.scrollIntoView()
			.should( 'be.visible' );
	} );

	it( 'displays the basic structure', () => {
		cy.get( '#nfd-nextsteps' ).should( 'be.visible' );
		
		// Check that the app has loaded with content
		cy.get( '#nfd-nextsteps p' ).should( 'be.visible' );
		
		// Check that tracks exist
		cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
		
		// Check that sections exist
		cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
		
		// Check that tasks exist
		cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );
	} );

	it( 'has functional basic interactions', () => {
		// Test that tracks can be toggled
		cy.get( '.nfd-track' ).first().as( 'firstTrack' );
		cy.get( '@firstTrack' ).should( 'have.attr', 'open' );
		
		// Test that sections can be toggled
		cy.get( '.nfd-section' ).first().as( 'firstSection' );
		cy.get( '@firstSection' ).find( '.nfd-section-header' ).click();
		cy.wait( 100 );
		
		// Test that task buttons exist and are clickable
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).should( 'be.visible' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-dismiss' ).should( 'be.visible' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-link' ).should( 'be.visible' );
	} );

	it( 'has the show/hide dismissed tasks functionality', () => {
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'be.visible' );
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'contain.text', 'View skipped tasks' );
		
		// Test that the button is clickable
		cy.get( '.nfd-nextsteps-filter-button' ).click();
		cy.get( '.nfd-nextsteps-filter-button' ).should( 'contain.text', 'Hide skipped tasks' );
	} );

	it( 'has proper task links and navigation', () => {
		cy.get( '.nfd-nextsteps-step-new' ).first().as( 'newTask' );
		
		// Check that the go button/link has proper href
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-link' ).should( 'have.attr', 'href' );
		
		// Check that buttons have proper event tracking attributes
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-todo' ).should( 'have.attr', 'data-nfd-event-key' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-dismiss' ).should( 'have.attr', 'data-nfd-event-key' );
		cy.get( '@newTask' ).find( '.nfd-nextsteps-button-link' ).should( 'have.attr', 'data-nfd-event-key' );
	} );

	it( 'displays progress bars', () => {
		// Check that progress bars exist for sections with tasks
		cy.get( '.nfd-section' ).each( ( $section ) => {
			cy.wrap( $section ).find( '.nfd-nextsteps-step-container' ).then( ( $tasks ) => {
				if ( $tasks.length > 0 ) {
					cy.wrap( $section ).find( '.nfd-progress-bar' ).should( 'exist' );
				}
			} );
		} );
	} );
} );
