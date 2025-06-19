// <reference types="Cypress" />
import { wpLogin } from '../wp-module-support/utils.cy';

describe( 'Next Steps App in Widget', { testIsolation: true }, () => {
	beforeEach( () => {
		wpLogin();
		cy.visit( '/wp-admin/index.php' );
	} );

	it( 'Next steps widget displays on dashboard', () => {
		cy.visit( '/wp-admin/index.php' );

		cy.get( '#nfd_next_steps_widget' )
			.scrollIntoView()
			.should( 'be.visible' );

		cy.get( '#nfd_next_steps_widget h2' ).contains( 'Next Steps' );

		cy.get( '#nfd_next_steps_widget .nfd-widget-next-steps' )
			.scrollIntoView()
			.should( 'be.visible' );
	} );
} );
