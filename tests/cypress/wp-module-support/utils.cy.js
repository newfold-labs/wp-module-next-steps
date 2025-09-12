const testPlan = require( '../fixtures/test-plan.json' );
const testCardsPlan = require( '../fixtures/test-cards-plan.json' );

/**
 * Loginto WordPress.
 */
export const wpLogin = () => {
	cy.login( Cypress.env( 'wpUsername' ), Cypress.env( 'wpPassword' ) );
};

/**
 * wp-cli helper
 *
 * This wraps the command in the required npx wp-env run cli wp
 *
 * @param {string} cmd               the command to send to wp-cli
 * @param          failOnNonZeroExit
 */
export const wpCli = ( cmd, failOnNonZeroExit = true ) => {
	const args = {
		env: {
			NODE_TLS_REJECT_UNAUTHORIZED: '1',
		},
	};
	if ( ! failOnNonZeroExit ) {
		args.failOnNonZeroExit = false;
	}
	cy.exec( `npx wp-env run cli wp ${ cmd }`, args ).then( ( result ) => {
		for ( const [ key, value ] of Object.entries( result ) ) {
			cy.log( `${ key }: ${ value }` );
		}
	} );
};

/**
 * Set next steps test fixture to database option
 */
export const setTestNextStepsData = () => {
	wpCli(
		`option update nfd_next_steps '${ JSON.stringify(
			testPlan
		) }' --format=json`
	);
};

/**
 * Set next steps test fixture to database option
 */
export const setTestCardsNextStepsData = () => {
	wpCli(
		`option update nfd_next_steps '${ JSON.stringify(
			testCardsPlan
		) }' --format=json`
	);
};

/**
 * Reset test data for clean test state
 */
export const resetNextStepsData = () => {
	// Use cy.exec to run wp-cli commands through wp-env
	wpCli( 'option delete nfd_next_steps', { failOnNonZeroExit: false } );
};
