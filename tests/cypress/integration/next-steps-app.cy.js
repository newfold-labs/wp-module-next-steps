// <reference types="Cypress" />
import { wpLogin } from '../wp-module-support/utils.cy';

describe( 'Next Steps App in plugin', { testIsolation: true }, () => {
	beforeEach( () => {
		wpLogin();
		cy.visit( '/wp-admin/index.php' );
	} );

	// test not connected to hiive will not have a solution
	it( 'Solutions page displays upgrade for those with no solution', () => {
		cy.visit( '/wp-admin/admin.php?page=next-steps' );

		cy.get( '#nfd-next-steps' ).scrollIntoView().should( 'be.visible' );
	} );
} );
