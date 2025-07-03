// <reference types="Cypress" />
import { wpLogin } from '../wp-module-support/utils.cy';

describe( 'Next Steps Portal in Plugin App', { testIsolation: true }, () => {
	beforeEach( () => {
		wpLogin();
		cy.visit(
			'/wp-admin/admin.php?page=' + Cypress.env( 'pluginId' ) + '#/home'
		);
	} );

	it( 'Next steps app displays on home in portal', () => {
		cy.get( '#next-steps-slot .next-steps-fill #nfd-nextsteps' )
			.scrollIntoView()
			.should( 'be.visible' );
	} );
} );
