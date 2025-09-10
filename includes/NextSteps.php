<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\Data\HiiveConnection;

/**
 * NextSteps - Main module class for managing next steps functionality
 *
 * This class handles all the core functionalities for the Next Steps module,
 * including widget and portal rendering, asset management, and dynamic redirects
 * for plugin-dependent tasks.
 *
 * Key Features:
 * - Next Steps widget and portal rendering
 * - Asset management and localization
 * - Dynamic redirect system for plugin-dependent tasks
 * - Plugin status detection and configuration checking
 * - Fallback redirects to plugin marketplace
 *
 * Dynamic Redirect System:
 * The module includes a sophisticated redirect system that can handle tasks
 * that depend on partner plugins. It checks plugin installation, activation,
 * and configuration status, then redirects users to appropriate URLs.
 *
 * Usage Examples:
 * - Jetpack with defaults: admin.php?page=redirect-check&p=jetpack
 * - WooCommerce with custom URLs: 'admin.php?page=redirect-check&p=woocommerce&r=' . urlencode( 'admin.php?page=wc-orders' ) . '&f=' . urlencode( 'admin.php?page=plugin-install.php?s=woocommerce' )
 * - Yoast SEO with defaults: admin.php?page=redirect-check&p=yoast-seo
 *
 * @package NewfoldLabs\WP\Module\NextSteps
 * @since 1.0.0
 * @author Newfold Labs
 */
class NextSteps {
	/**
	 * Dependency injection container.
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Steps API class instance.
	 *
	 * @var StepsApi
	 */
	protected static $steps_api;

	/**
	 * Constructor for the NextSteps class.
	 *
	 * @param Container $container The module container.
	 */
	public function __construct( Container $container ) {
		// Autoloader handles class loading
		new PlanLoader();
		$hiive           = new HiiveConnection();
		self::$steps_api = new StepsApi( $hiive );
		$this->container = $container;
		\add_action( 'rest_api_init', array( $this, 'init_steps_apis' ) );
		\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'nextsteps_widget' ) );
		\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'nextsteps_portal' ) );
		// dummy page to manage dynamic redirects
		\add_action( 'admin_menu', array( __CLASS__, 'add_redirect_link' ) );
		\add_action( 'admin_init', array( __CLASS__, 'check_redirect' ) );

		new I18nService( $container );
		if ( is_admin() ) {
			new NextStepsWidget();
		}
	}

	/**
	 * Initialize the Entitilement API Controller.
	 */
	public function init_steps_apis(): void {
		self::$steps_api->register_routes();
	}

	/**
	 * Add to the Newfold subnav.
	 *
	 * @param array $subnav The nav array.
	 * @return array The filtered nav array
	 */
	public static function add_nfd_subnav( $subnav ) {
		$next_steps = array(
			'title'    => __( 'Next Steps', 'wp-module-next-steps' ),
			'route'    => 'next-steps',
			'priority' => 10,
			'callback' => array( __CLASS__, 'render_next_steps_page' ),
		);
		array_push( $subnav, $next_steps );
		return $subnav;
	}

	/**
	 * Render "NextSteps" page root
	 *
	 * @return void
	 */
	public static function render_next_steps_page() {
		echo '<div id="nfd-next-steps-app"></div>';
	}

	/**
	 * Enqueue widget app assets.
	 */
	public static function nextsteps_widget() {
		// Always register assets for extensibility (other modules might depend on them)
		$asset_file = NFD_NEXTSTEPS_DIR . '/build/next-steps-widget/bundle.asset.php';
		$build_dir  = NFD_NEXTSTEPS_PLUGIN_URL . 'vendor/newfold-labs/wp-module-next-steps/build/next-steps-widget/';

		if ( is_readable( $asset_file ) ) {
			$asset = include_once $asset_file;
		} else {
			return;
		}

		\wp_register_script(
			'next-steps-widget',
			$build_dir . 'bundle.js',
			array_merge(
				$asset['dependencies'],
				array( 'newfold-hiive-events' ),
			),
			$asset['version'],
			true
		);

		\wp_register_style(
			'next-steps-widget-style',
			$build_dir . 'next-steps-widget.css',
			array( 'bluehost-style' ),
			$asset['version']
		);

		// Only enqueue on dashboard pages
		$screen = \get_current_screen();
		if ( isset( $screen->id ) &&
			false !== strpos( $screen->id, 'dashboard' ) && // on dashboard page
			false === strpos( $screen->id, 'nfd-onboarding' ) // but not onboarding page
		) {
			\wp_enqueue_script( 'next-steps-widget' );
			\wp_enqueue_style( 'next-steps-widget-style' );

			// Get current plan data
			$current_plan    = PlanManager::get_current_plan();
			$next_steps_data = $current_plan ? $current_plan->to_array() : array();

			\wp_localize_script(
				'next-steps-widget',
				'NewfoldNextSteps',
				$next_steps_data
			);
		}
	}

	/**
	 * Enqueue Fill app assets.
	 */
	public static function nextsteps_portal() {
		// Always register assets for extensibility (other modules might depend on them)
		$asset_file = NFD_NEXTSTEPS_DIR . '/build/next-steps-portal/bundle.asset.php';
		$build_dir  = NFD_NEXTSTEPS_PLUGIN_URL . 'vendor/newfold-labs/wp-module-next-steps/build/next-steps-portal/';

		if ( is_readable( $asset_file ) ) {
			$asset = include_once $asset_file;
		} else {
			return;
		}

		\wp_register_script(
			'next-steps-portal',
			$build_dir . 'bundle.js',
			array_merge(
				$asset['dependencies'],
				array( 'newfold-hiive-events', 'bluehost-script', 'nfd-portal-registry' ),
			),
			$asset['version'],
			true
		);

		\wp_register_style(
			'next-steps-portal-style',
			$build_dir . 'next-steps-portal.css',
			null, // still dependant on plugin styles but they are loaded on the plugin page
			$asset['version']
		);

		// Only enqueue on plugin pages
		$screen = \get_current_screen();
		if ( isset( $screen->id ) && false !== strpos( $screen->id, 'bluehost' ) ) {
			\wp_enqueue_script( 'next-steps-portal' );
			\wp_enqueue_style( 'next-steps-portal-style' );

			// Get current plan data
			$current_plan    = PlanManager::get_current_plan();
			$next_steps_data = $current_plan ? $current_plan->to_array() : array();

			\wp_localize_script(
				'next-steps-portal',
				'NewfoldNextSteps',
				$next_steps_data
			);
		}
	}

	/**
	 * Checks partner plugin status and redirect the user accordingly.
	 *
	 * This method handles dynamic redirects for next steps tasks that depend on
	 * partner plugins. It checks if the plugin is installed and active, then
	 * redirects to the appropriate URL based on the plugin's status.
	 *
	 * URL Parameters:
	 * - p: Plugin slug to check (required)
	 * - r: URL to redirect to if plugin is active (optional - uses default from whitelist)
	 * - f: URL to redirect to if plugin is not active (optional - uses default from whitelist)
	 *
	 * @return void
	 */
	public static function check_redirect() {
		// Only process redirect requests
		if (
			! is_admin() ||
			! isset( $_GET['page'] ) ||
			'redirect-check' !== $_GET['page']
		) {
			return;
		}

		// Sanitize and validate parameters
		$plugin_slug = isset( $_GET['p'] ) ? sanitize_text_field( wp_unslash( $_GET['p'] ) ) : '';
		$redirect_url = isset( $_GET['r'] ) ? esc_url_raw( wp_unslash( $_GET['r'] ) ) : '';
		$fallback_url = isset( $_GET['f'] ) ? esc_url_raw( wp_unslash( $_GET['f'] ) ) : '';

		// Validate required plugin parameter
		if ( empty( $plugin_slug ) ) {
			// Redirect to admin dashboard if no plugin specified
			wp_safe_redirect( admin_url() );
			exit;
		}

		// Get partner plugin defaults
		$partner_plugins = self::get_partner_plugins();
		if ( ! array_key_exists( $plugin_slug, $partner_plugins ) ) {
			// Redirect to admin dashboard if invalid plugin specified
			wp_safe_redirect( admin_url() );
			exit;
		}

		// Use provided URLs or fall back to defaults
		$final_redirect_url = ! empty( $redirect_url ) ? $redirect_url : admin_url( $partner_plugins[ $plugin_slug ]['redirect_url'] );
		$final_fallback_url = ! empty( $fallback_url ) ? $fallback_url : admin_url( $partner_plugins[ $plugin_slug ]['fallback_url'] );

		// Check plugin status
		$plugin_active = self::check_plugin( $plugin_slug );
		
		// Determine redirect URL based on plugin status
		$final_redirect_url = $plugin_active ? $final_redirect_url : $final_fallback_url;

		// Perform redirect
		wp_safe_redirect( $final_redirect_url );
		exit;
	}

	/**
	 * Get the partner plugins
	 *
	 * @return array Partner plugins
	 */
	private static function get_partner_plugins() {
		// key value pair of plugin slug and plugin file
		return array( 
			'jetpack' => array(
				'class' => '\Automattic\Jetpack\Connection\Manager',
				'file' => 'jetpack/jetpack.php',
				'redirect_url' => 'admin.php?page=my-jetpack#add-boost',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=jetpack',
			),
			'woocommerce' => array(
				'file' => 'woocommerce/woocommerce.php',
				'redirect_url' => 'admin.php?page=wc-settings&tab=advanced',
				'fallback_url' => 'plugin-install.php?s=woocommerce&tab=search&type=term',
			),
			'yoast-seo' => array(
				'file' => 'wordpress-seo/wp-seo.php',
				'redirect_url' => 'admin.php?page=wpseo_dashboard',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=yoast',
			),
			'advanced-reviews' => array(
				'file' => 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php',
				'redirect_url' => 'admin.php?page=advanced-reviews',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=advanced+reviews',
			),
			'affiliates' => array(
				'file' => 'wp-plugin-affiliates/wp-plugin-affiliates.php',
				'redirect_url' => 'admin.php?page=affiliates',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=affiliate',
			),
			'gift-cards' => array(
				'file' => 'yith-woocommerce-gift-cards-premium/init.php',
				'redirect_url' => 'admin.php?page=gift-cards',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=gift+cards',
			),
			'email-templates' => array(
				'file' => 'wp-plugin-email-templates/wp-plugin-email-templates.php',
				'redirect_url' => 'edit.php?post_type=bh-email-template',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=email+templates',
			),
		);
	}


	/**
	 * Check if a plugin is properly configured (plugin-specific logic)
	 *
	 * @param string $plugin_slug The plugin slug
	 * @return bool Whether the plugin is active and/or configured
	 */
	private static function check_plugin( $plugin_slug ) {
		// Ensure WordPress plugin functions are available
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$partner_plugins = self::get_partner_plugins();

		// Check if plugin slug is in our whitelist
		if ( ! array_key_exists( $plugin_slug, $partner_plugins ) ) {
			return false;
		}

		// Plugin-specific configuration checks
		switch ( $plugin_slug ) {
			case 'jetpack':
				// Check if Jetpack is both active AND connected
				if (
					is_plugin_active( $partner_plugins[$plugin_slug]['file'] ) &&
					class_exists( '\Automattic\Jetpack\Connection\Manager' )
				) {
					$manager = new \Automattic\Jetpack\Connection\Manager();
					return $manager->is_connected();
				}
				return false;

			default:
				// For other plugins, just check if they're active
				return is_plugin_active( $partner_plugins[$plugin_slug]['file'] );
		}
	}


	/**
	 * Registers a hidden submenu page for checking partner plugin status and redirecting.
	 */
	public static function add_redirect_link() {
		\add_submenu_page(
			null, // No parent, so it won't appear in any menu
			'Checking Partner Plugin Before Redirect',
			'',
			'manage_options',
			'redirect-check',
			array( __CLASS__, 'check_redirect' ),
		);
	}
}
