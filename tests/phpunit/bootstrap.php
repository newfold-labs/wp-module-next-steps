<?php
/**
 * Bootstrap file for Next Steps module unit tests.
 *
 * @package WPModuleNextSteps
 */

// Load up Composer dependencies
require dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';

// Define WordPress constants for tests
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

// Try to find WordPress PHPUnit environment
$wp_phpunit_dir = getenv( 'WP_PHPUNIT__DIR' ) ?: getenv( 'WP_PHPUNIT_DIR' );

// If WordPress PHPUnit environment is available, use it
if ( $wp_phpunit_dir && file_exists( $wp_phpunit_dir . '/includes/bootstrap.php' ) ) {
	// Bootstrap WordPress tests
	require $wp_phpunit_dir . '/includes/bootstrap.php';
	
	// Load the module bootstrap after WordPress is set up
	require dirname( dirname( __DIR__ ) ) . '/bootstrap.php';
} else {
	// For basic testing without full WordPress environment
	// Define minimal WordPress-like functions for testing
	if ( ! function_exists( 'get_option' ) ) {
		function get_option( $option, $default = false ) {
			static $options = array();
			return isset( $options[ $option ] ) ? $options[ $option ] : $default;
		}
	}
	
	if ( ! function_exists( 'update_option' ) ) {
		function update_option( $option, $value ) {
			static $options = array();
			$old_value = isset( $options[ $option ] ) ? $options[ $option ] : false;
			$options[ $option ] = $value;
			// Trigger hook if it exists
			if ( function_exists( 'do_action' ) ) {
				do_action( "update_option_{$option}", $old_value, $value );
			}
			return true;
		}
	}
	
	if ( ! function_exists( 'delete_option' ) ) {
		function delete_option( $option ) {
			static $options = array();
			unset( $options[ $option ] );
			return true;
		}
	}
	
	// WordPress translation function
	if ( ! function_exists( '__' ) ) {
		function __( $text, $domain = 'default' ) {
			return $text;
		}
	}
	
	// WordPress plugin functions
	if ( ! function_exists( 'is_plugin_active' ) ) {
		function is_plugin_active( $plugin ) {
			static $active_plugins = array();
			return in_array( $plugin, $active_plugins );
		}
	}
	
	if ( ! function_exists( 'post_type_exists' ) ) {
		function post_type_exists( $post_type ) {
			static $post_types = array( 'post', 'page', 'attachment' );
			return in_array( $post_type, $post_types );
		}
	}
	
	// WordPress page functions
	if ( ! function_exists( 'get_page_by_path' ) ) {
		function get_page_by_path( $page_path, $output = OBJECT, $post_type = 'page' ) {
			return false; // No pages exist in test environment
		}
	}
	
	// WordPress user functions
	if ( ! function_exists( 'count_users' ) ) {
		function count_users() {
			return array( 'total_users' => 1 ); // Single user in test environment
		}
	}
	
	// WordPress site info functions
	if ( ! function_exists( 'get_bloginfo' ) ) {
		function get_bloginfo( $show = '', $filter = 'raw' ) {
			$blog_info = array(
				'name' => 'Test Site',
				'description' => 'A test WordPress site',
			);
			return isset( $blog_info[ $show ] ) ? $blog_info[ $show ] : '';
		}
	}
	
	// WordPress hook functions
	if ( ! function_exists( 'has_action' ) ) {
		function has_action( $tag, $function_to_check = false ) {
			static $actions = array();
			if ( $function_to_check === false ) {
				return isset( $actions[ $tag ] );
			}
			return isset( $actions[ $tag ] ) && in_array( $function_to_check, $actions[ $tag ] );
		}
	}
	
	if ( ! function_exists( 'add_action' ) ) {
		function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			static $actions = array();
			if ( ! isset( $actions[ $tag ] ) ) {
				$actions[ $tag ] = array();
			}
			$actions[ $tag ][] = $function_to_add;
			return true;
		}
	}
	
	if ( ! function_exists( 'do_action' ) ) {
		function do_action( $tag, ...$args ) {
			// Simple action trigger for tests
			return true;
		}
	}
	
	// Add minimal WordPress test case
	if ( ! class_exists( 'WP_UnitTestCase' ) ) {
		class WP_UnitTestCase extends PHPUnit\Framework\TestCase {
			public function setUp(): void {
				parent::setUp();
			}
			
			public function tearDown(): void {
				parent::tearDown();
			}
		}
	}
	
	// Load the module classes
	require dirname( dirname( __DIR__ ) ) . '/bootstrap.php';
} 