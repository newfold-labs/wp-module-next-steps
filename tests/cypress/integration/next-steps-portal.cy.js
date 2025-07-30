// <reference types="Cypress" />
import { wpLogin } from '../wp-module-support/utils.cy';
import {
	resetNextStepsData,
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

                // === Task Links and Navigation ===
                getTaskByStatus( 'new' ).first().then( ( task ) => {
                        verifyTaskLinks( cy.wrap( task ) );
                        verifyTaskDataAttributes( cy.wrap( task ) );
                } );
        } );

        // New test to verify version handling in portal context
        it( 'handles versioned data correctly in portal', () => {
                // Wait for initial load
                waitForNextStepsApp();
                
                // Verify basic functionality works
                getTaskByStatus( 'new' ).should( 'have.length.greaterThan', 0 );
                
                // Refresh the page to trigger potential merge logic
                cy.reload();
                
                // Wait for portal to reload
                cy.get( '.next-steps-fill #nfd-nextsteps' )
                        .scrollIntoView()
                        .should( 'be.visible' );
                        
                waitForNextStepsApp();
                
                // Verify the app still functions normally after reload
                cy.get( '.nfd-track' ).should( 'have.length.greaterThan', 0 );
                cy.get( '.nfd-section' ).should( 'have.length.greaterThan', 0 );
                cy.get( '.nfd-nextsteps-step-container' ).should( 'have.length.greaterThan', 0 );
        } );
} );
