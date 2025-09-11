<?php
/**
 * Bootstrap file for Next Steps module unit tests.
 *
 * @package WPModuleNextSteps
 *
 * @phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed
 * @phpcs:disable Universal.NamingConventions.NoReservedKeywordParameterNames.defaultFound
 * @phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
 * @phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
 * 
 */

// Load up Composer dependencies.
require dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';

// Define WordPress constants for tests.
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

// Try to find WordPress PHPUnit environment.
$wp_phpunit_dir = getenv( 'WP_PHPUNIT__DIR' ) ? getenv( 'WP_PHPUNIT__DIR' ) : getenv( 'WP_PHPUNIT_DIR' );

// If WordPress PHPUnit environment is available, use it.
if ( $wp_phpunit_dir && file_exists( $wp_phpunit_dir . '/includes/bootstrap.php' ) ) {
	// Bootstrap WordPress tests.
	require $wp_phpunit_dir . '/includes/bootstrap.php';

	// Load the module bootstrap after WordPress is set up.
	require dirname( dirname( __DIR__ ) ) . '/bootstrap.php';
} else {
	// For basic testing without full WordPress environment.
	// Define minimal WordPress-like functions for testing.
	// Global option storage for WordPress functions.
	if ( ! isset( $GLOBALS['test_wp_options'] ) ) {
		$GLOBALS['test_wp_options'] = array();
	}

	if ( ! function_exists( 'get_option' ) ) {
	/**
	 * Retrieves an option value based on an option name.
	 *
	 * @param string $option  Name of option to retrieve.
	 * @param mixed  $default Optional. Default value to return if the option does not exist.
	 * @return mixed Value set for the option.
	 */
	function get_option( $option, $default = false ) {
			return isset( $GLOBALS['test_wp_options'][ $option ] ) ? $GLOBALS['test_wp_options'][ $option ] : $default;
		}
	}

	if ( ! function_exists( 'update_option' ) ) {
		/**
		 * Updates the value of an option.
		 *
		 * @param string $option Option name.
		 * @param mixed  $value  Option value.
		 * @return bool True on successful update, false on failure.
		 */
		function update_option( $option, $value ) {
			$old_value                             = isset( $GLOBALS['test_wp_options'][ $option ] ) ? $GLOBALS['test_wp_options'][ $option ] : false;
			$GLOBALS['test_wp_options'][ $option ] = $value;
			// Trigger hook if it exists.
			if ( function_exists( 'do_action' ) ) {
				do_action( "update_option_{$option}", $old_value, $value );
			}
			return true;
		}
	}

	if ( ! function_exists( 'delete_option' ) ) {
		/**
		 * Removes an option by name.
		 *
		 * @param string $option Name of option to remove.
		 * @return bool True on successful removal, false on failure.
		 */
		function delete_option( $option ) {
			unset( $GLOBALS['test_wp_options'][ $option ] );
			return true;
		}
	}

	// Global transient storage for WordPress functions.
	if ( ! isset( $GLOBALS['test_wp_transients'] ) ) {
		$GLOBALS['test_wp_transients'] = array();
	}

	// WordPress transient functions.
	if ( ! function_exists( 'get_transient' ) ) {
		/**
		 * Retrieves the value of a transient.
		 *
		 * @param string $transient Transient name.
		 * @return mixed Value of the transient, or false on failure.
		 */
		function get_transient( $transient ) {
			return isset( $GLOBALS['test_wp_transients'][ $transient ] ) ? $GLOBALS['test_wp_transients'][ $transient ] : false;
		}
	}

	if ( ! function_exists( 'set_transient' ) ) {
	/**
	 * Sets/updates the value of a transient.
	 *
	 * @param string $transient  Transient name.
	 * @param mixed  $value      Transient value.
	 * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
	 * @return bool True on successful set, false on failure.
	 */
	function set_transient( $transient, $value, $expiration = 0 ) {
			$GLOBALS['test_wp_transients'][ $transient ] = $value;
			return true;
		}
	}

	if ( ! function_exists( 'delete_transient' ) ) {
		/**
		 * Deletes a transient.
		 *
		 * @param string $transient Transient name.
		 * @return bool True on successful deletion, false on failure.
		 */
		function delete_transient( $transient ) {
			unset( $GLOBALS['test_wp_transients'][ $transient ] );
			return true;
		}
	}

	// WordPress translation function.
	if ( ! function_exists( '__' ) ) {
	/**
	 * Retrieve the translation of $text.
	 *
	 * @param string $text   Text to translate.
	 * @param string $domain Optional. Text domain. Default 'default'.
	 * @return string Translated text.
	 */
	function __( $text, $domain = 'default' ) {
			return $text;
		}
	}

	// WordPress plugin functions.
	if ( ! function_exists( 'is_plugin_active' ) ) {
		/**
		 * Determines whether a plugin is active.
		 *
		 * @param string $plugin Path to the plugin file relative to the plugins directory.
		 * @return bool True if the plugin is active, false otherwise.
		 */
		function is_plugin_active( $plugin ) {
			static $active_plugins = array();
			return in_array( $plugin, $active_plugins, true );
		}
	}

	if ( ! function_exists( 'post_type_exists' ) ) {
		/**
		 * Determines whether a post type is registered.
		 *
		 * @param string $post_type Post type name.
		 * @return bool Whether post type is registered.
		 */
		function post_type_exists( $post_type ) {
			static $post_types = array( 'post', 'page', 'attachment' );
			return in_array( $post_type, $post_types, true );
		}
	}

	// WordPress page functions.
	if ( ! function_exists( 'get_page_by_path' ) ) {
	/**
	 * Retrieves a page given its path.
	 *
	 * @param string       $page_path  Page path.
	 * @param string       $output     Optional. The required return type. Default OBJECT.
	 * @param string|array $post_type  Optional. Post type or array of post types. Default 'page'.
	 * @return WP_Post|array|null WP_Post (or array) on success, or null on failure.
	 */
	function get_page_by_path( $page_path, $output = OBJECT, $post_type = 'page' ) {
			return false; // No pages exist in test environment.
		}
	}

	// WordPress user functions.
	if ( ! function_exists( 'count_users' ) ) {
		/**
		 * Counts number of users.
		 *
		 * @return array Users count data.
		 */
		function count_users() {
			return array( 'total_users' => 1 ); // Single user in test environment.
		}
	}

	// WordPress site info functions.
	if ( ! function_exists( 'get_bloginfo' ) ) {
	/**
	 * Retrieves information about the current site.
	 *
	 * @param string $show   Optional. Site info to retrieve. Default empty (site name).
	 * @param string $filter Optional. How to filter what is retrieved. Default 'raw'.
	 * @return string Requested information.
	 */
	function get_bloginfo( $show = '', $filter = 'raw' ) {
			$blog_info = array(
				'name'        => 'Test Site',
				'description' => 'A test WordPress site',
			);
			return isset( $blog_info[ $show ] ) ? $blog_info[ $show ] : '';
		}
	}

	// WordPress admin functions.
	if ( ! function_exists( 'is_admin' ) ) {
		/**
		 * Determines whether the current request is for an administrative interface page.
		 *
		 * @return bool True if inside WordPress administration interface, false otherwise.
		 */
		function is_admin() {
			return true; // Assume admin in test environment
		}
	}

	if ( ! function_exists( 'wp_doing_ajax' ) ) {
		/**
		 * Determines whether the current request is a WordPress Ajax request.
		 *
		 * @return bool True if it's a WordPress Ajax request, false otherwise.
		 */
		function wp_doing_ajax() {
			return false; // Assume not AJAX in test environment
		}
	}

	// WordPress cache functions.
	if ( ! function_exists( 'wp_cache_delete' ) ) {
	/**
	 * Removes the cache contents matching key and group.
	 *
	 * @param int|string $key   What the contents in the cache are called.
	 * @param string     $group Optional. Where the cache contents are grouped. Default empty.
	 * @return bool True on successful removal, false on failure.
	 */
	function wp_cache_delete( $key, $group = '' ) {
			return true; // Mock cache deletion
		}
	}

	// Global hook storage for WordPress functions.
	if ( ! isset( $GLOBALS['test_wp_hooks'] ) ) {
		$GLOBALS['test_wp_hooks'] = array();
	}

	// WordPress hook functions.
	if ( ! function_exists( 'has_action' ) ) {
		/**
		 * Checks if any action has been registered for a hook.
		 *
		 * @param string         $tag               The name of the action hook.
		 * @param callable|false $function_to_check Optional. The callback to check for. Default false.
		 * @return bool|int If function_to_check is omitted, returns boolean for whether the hook has callbacks.
		 *                  If function_to_check is specified, returns the priority of that callback or false if not attached.
		 */
		function has_action( $tag, $function_to_check = false ) {
			if ( ! isset( $GLOBALS['test_wp_hooks'][ $tag ] ) ) {
				return false;
			}

			if ( false === $function_to_check ) {
				return count( $GLOBALS['test_wp_hooks'][ $tag ] );
			}

			// Check for specific callback.
			foreach ( $GLOBALS['test_wp_hooks'][ $tag ] as $hook_data ) {
				if ( $hook_data['callback'] === $function_to_check ) {
					return $hook_data['priority'];
				}
			}
			return false;
		}
	}

	if ( ! function_exists( 'add_action' ) ) {
		/**
		 * Hooks a function on to a specific action.
		 *
		 * @param string   $tag             The name of the action to hook the function_to_add callback to.
		 * @param callable $function_to_add The callback to be run when the action is called.
		 * @param int      $priority        Optional. Used to specify the order. Default 10.
		 * @param int      $accepted_args   Optional. The number of arguments the callback accepts. Default 1.
		 * @return true Always returns true.
		 */
		function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			if ( ! isset( $GLOBALS['test_wp_hooks'][ $tag ] ) ) {
				$GLOBALS['test_wp_hooks'][ $tag ] = array();
			}
			$GLOBALS['test_wp_hooks'][ $tag ][] = array(
				'callback' => $function_to_add,
				'priority' => $priority,
				'args'     => $accepted_args,
			);
			return true;
		}
	}

	if ( ! function_exists( 'do_action' ) ) {
		/**
		 * Calls the callback functions that have been added to an action hook.
		 *
		 * @param string $tag  The name of the action to be executed.
		 * @param mixed  ...$args Optional. Additional arguments passed to callbacks.
		 * @return void
		 */
		function do_action( $tag, ...$args ) {
			// Simple action trigger for tests.
		}
	}

	// Add minimal WordPress test case.
	if ( ! class_exists( 'WP_UnitTestCase' ) ) {
		/**
		 * Minimal WordPress test case for unit testing.
		 */
		class WP_UnitTestCase extends PHPUnit\Framework\TestCase {
		/**
		 * Set up the test case.
		 *
		 * @return void
		 */
		public function setUp(): void {
			parent::setUp();
		}

		/**
		 * Tear down the test case.
		 *
		 * @return void
		 */
		public function tearDown(): void {
			parent::tearDown();
		}
		}
	}

	// Load the module classes.
	require dirname( dirname( __DIR__ ) ) . '/bootstrap.php';
}
