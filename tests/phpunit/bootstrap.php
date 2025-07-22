<?php
/**
 * Bootstrap file for Next Steps module unit tests.
 *
 * @package WPModuleNextSteps
 */

// Load up Composer dependencies
require dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';

$wp_phpunit_dir = getenv( 'WP_PHPUNIT__DIR' );

// Bootstrap tests
require $wp_phpunit_dir . '/includes/bootstrap.php';

// Load the module bootstrap after WordPress is set up
require dirname( dirname( __DIR__ ) ) . '/bootstrap.php'; 