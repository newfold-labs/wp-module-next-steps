<?php
/**
 * Plugin Redirect Handler
 *
 * Handles dynamic redirects for next steps tasks that depend on partner plugins.
 * Checks plugin activation, and configuration status, then redirects
 * users to appropriate URLs based on the plugin's status.
 *
 * Features:
 * - Whitelist-based plugin validation for security
 * - Default URLs for each partner plugin
 * - Optional parameter overrides for custom URLs
 * - Optional plugin-specific configuration checks (e.g., Jetpack connection)
 * - Graceful error handling with fallback redirects
 *
 * Usage Examples (find more in the plan data files):
 * - Jetpack with defaults: admin.php?page=redirect-check&p=jetpack
 * - Yoast SEO with defaults: admin.php?page=redirect-check&p=yoast-seo
 * - WooCommerce with custom URLs: 'admin.php?page=redirect-check&p=woocommerce&r=' . urlencode( 'admin.php?page=wc-orders' ) . '&f=' . urlencode( 'admin.php?page=plugin-install.php?s=woocommerce' )
 *
 * @package NewfoldLabs\WP\Module\NextSteps
 * @since 1.0.0
 * @author Newfold Labs
 */
class PluginRedirect {

	/**
	 * Initialize the plugin redirect functionality
	 */
	public static function init() {
		// dummy page to manage dynamic redirects
		add_action( 'admin_menu', array( __CLASS__, 'add_redirect_page' ) );
	}

	/**
	 * Partner plugin whitelist with default redirect and fallback URLs
	 *
	 * @return array Partner plugins configuration
	 */
	private static function get_partner_plugins() {
		// key value pair of plugin slug and plugin configuration
		return array(
			'jetpack'          => array(
				'file'          => 'jetpack/jetpack.php',
				'redirect_url' => 'admin.php?page=my-jetpack#add-boost',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=jetpack',
			),
			'woocommerce'      => array(
				'file'          => 'woocommerce/woocommerce.php',
				'redirect_url' => 'admin.php?page=wc-settings&tab=advanced',
				'fallback_url' => 'plugin-install.php?s=woocommerce&tab=search&type=term',
			),
			'yoast-seo'        => array(
				'file'          => 'wordpress-seo/wp-seo.php',
				'redirect_url' => 'admin.php?page=wpseo_dashboard',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=yoast',
			),
			'advanced-reviews' => array(
				'file'          => 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php',
				'redirect_url' => 'admin.php?page=advanced-reviews',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=advanced+reviews',
			),
			'affiliates'        => array(
				'file'          => 'wp-plugin-affiliates/wp-plugin-affiliates.php',
				'redirect_url' => 'admin.php?page=affiliates',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=affiliate',
			),
			'gift-cards'       => array(
				'file'          => 'yith-woocommerce-gift-cards-premium/init.php',
				'redirect_url' => 'admin.php?page=gift-cards',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=gift+cards',
			),
			'email-templates'  => array(
				'file'          => 'wp-plugin-email-templates/wp-plugin-email-templates.php',
				'redirect_url' => 'edit.php?post_type=bh-email-template',
				'fallback_url' => 'admin.php?page=solutions&category=all&s=email+templates',
			),
			'akismet'          => array(
				'file'          => 'akismet/akismet.php',
				'redirect_url' => 'admin.php?page=akismet-key-config',
				'fallback_url' => 'plugin-install.php?s=akismet&tab=search&type=term',
			),
		);
	}

	/**
	 * Registers a hidden submenu page for checking partner plugin status and redirecting.
	 */
	public static function add_redirect_page() {
		\add_submenu_page(
			null, // No parent, so it won't appear in any menu
			'Checking Partner Plugin Before Redirect',
			'',
			'manage_options',
			'redirect-check',
			array( __CLASS__, 'check_redirect' ),
		);
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
		$plugin_slug  = isset( $_GET['p'] ) ? sanitize_text_field( wp_unslash( $_GET['p'] ) ) : '';
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
					is_plugin_active( $partner_plugins[ $plugin_slug ]['file'] ) &&
					class_exists( '\Automattic\Jetpack\Connection\Manager' )
				) {
					$manager = new \Automattic\Jetpack\Connection\Manager();
					return $manager->is_connected();
				}
				return false;

			default:
				// For other plugins, just check if they're active
				return is_plugin_active( $partner_plugins[ $plugin_slug ]['file'] );
		}
	}
}
