<?php
/**
 * Plan Factory for Next Steps Data.
 *
 * @package WPPluginBluehost
 */

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\Data\Plans\StorePlan;
use NewfoldLabs\WP\Module\NextSteps\Data\Plans\BlogPlan;
use NewfoldLabs\WP\Module\NextSteps\Data\Plans\CorporatePlan;
use function NewfoldLabs\WP\ModuleLoader\container;
use function NewfoldLabs\WP\Context\getContext;

/**
 * PlanFactory
 *
 * Handles plan creation, site type detection, and WordPress hooks for the Next Steps module.
 * Responsible for creating and instantiating plans based on site context.
 */
class PlanFactory {

	/**
	 * Transient name for Newfold solutions data
	 */
	const SOLUTIONS_TRANSIENT = 'newfold_solutions';

	/**
	 * Option name for onboarding site info
	 */
	const ONBOARDING_SITE_INFO_OPTION = 'nfd_module_onboarding_site_info';

	/**
	 * Available plan types, this maps the site_type from onboarding module to internal plan types
	 *
	 * Maps nfd_module_onboarding_site_info['site_type'] values to internal plan types:
	 * - 'personal' (onboarding) -> 'blog' (internal plan)
	 * - 'business' (onboarding) -> 'corporate' (internal plan)
	 * - 'ecommerce' (onboarding) -> 'ecommerce' (internal plan)
	 */
	const PLAN_TYPES = array(
		'personal'  => 'blog',
		'business'  => 'corporate',
		'ecommerce' => 'ecommerce',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize default steps on init
		\add_action( 'init', array( __CLASS__, 'load_default_steps' ), 1 );

		// Hook into solution option changes for dynamic plan switching
		\add_action( 'update_option_' . self::ONBOARDING_SITE_INFO_OPTION, array( __CLASS__, 'on_sitetype_change' ), 10, 2 );

		// Hook into WooCommerce activation to potentially switch to store plan
		\add_action( 'activated_plugin', array( __CLASS__, 'on_woocommerce_activation' ), 10, 2 );

		\add_action( 'woocommerce_rest_insert_product_object', array( __CLASS__, 'on_product_creation' ), 10, 3 );

		// Hook into language changes to resync next steps data
		\add_action( 'update_option_WPLANG', array( __CLASS__, 'on_language_change' ), 10, 2 );
		\add_action( 'switch_locale', array( __CLASS__, 'on_locale_switch' ), 10, 1 );
	}

	/**
	 * Load default steps on init
	 *
	 * @return void
	 */
	public static function load_default_steps() {
		// Only load if we're in admin or doing AJAX
		if ( ! \is_admin() && ! \wp_doing_ajax() ) {
			return;
		}

		// Check if we already have a plan loaded
		$current_plan = PlanRepository::get_current_plan();
		if ( $current_plan ) {
			return;
		}

		// Load default plan based on site type
		$site_type    = self::determine_site_type();
		$default_plan = self::create_plan( $site_type );
		if ( $default_plan ) {
			PlanRepository::save_plan( $default_plan );
		}
	}

	/**
	 * Handle site type changes
	 *
	 * @param array $old_value The old site type
	 * @param array $new_value The new site type
	 * @return void
	 */
	public static function on_sitetype_change( $old_value, $new_value ) {
		// Check if values are each an array
		if (
			! is_array( $old_value ) ||
			! is_array( $new_value )
		) {
			return;
		}

		$old_site_type = array_key_exists( 'site_type', $old_value ) ? $old_value['site_type'] : '';
		$new_site_type = array_key_exists( 'site_type', $new_value ) ? $new_value['site_type'] : '';

		if ( $old_site_type === $new_site_type ) {
			return;
		}

		// Check if the new site type is valid
		if ( ! array_key_exists( $new_site_type, self::PLAN_TYPES ) ) {
			return; // Don't load any plan for invalid site types
		}

		// Convert onboarding site type to internal plan type
		$new_plan_type = self::PLAN_TYPES[ $new_site_type ];

		// Switch to the new plan
		PlanRepository::switch_plan( $new_plan_type );
	}

	/**
	 * Handle WooCommerce activation
	 *
	 * @param string $plugin The plugin name
	 * @param bool   $network_wide Whether the plugin is being activated on the network
	 * @return void
	 */
	public static function on_woocommerce_activation( $plugin, $network_wide ) {
		if ( 'woocommerce/woocommerce.php' !== $plugin ) {
			return;
		}

		// Switch to ecommerce plan when WooCommerce is activated
		PlanRepository::switch_plan( 'ecommerce' );
	}

	/**
	 * Handle product creation
	 *
	 * @param object $product The product object
	 * @param object $request The request object
	 * @param bool   $creating Whether the product is being created
	 * @return void
	 */
	public static function on_product_creation( $product, $request, $creating ) {
		if ( $creating ) {
			$current_plan = PlanRepository::get_current_plan();
			if ( $current_plan && 'ecommerce' === $current_plan->type ) {
				// Mark the "Add Products" section and task as complete
				$validtask    = $current_plan->update_task_status( 'store_build_track', 'setup_products', 'store_add_product', 'completed' );
				$validsection = $current_plan->update_section_status( 'store_build_track', 'setup_products', 'completed' );
				if ( $validtask && $validsection ) {
					PlanRepository::save_plan( $current_plan );
				}
			}
		}
	}

	/**
	 * Detect site type based on various factors
	 *
	 * @return string The detected site type
	 */
	public static function detect_site_type() {
		// Check if WooCommerce is active
		if ( self::is_ecommerce_site() ) {
			return 'ecommerce';
		}

		// Check if it's a corporate/business site
		if ( self::is_corporate_site() ) {
			return 'corporate';
		}

		// Default to blog/personal
		return 'blog';
	}

	/**
	 * Check if site is ecommerce
	 *
	 * @return bool Whether the site is ecommerce
	 */
	private static function is_ecommerce_site() {
		return \class_exists( 'WooCommerce' ) || \is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Check if site is corporate
	 *
	 * @return bool Whether the site is corporate
	 */
	private static function is_corporate_site() {
		// Check for business-related plugins or themes
		$business_plugins = array(
			'elementor-pro/elementor-pro.php',
			'wpforms/wpforms.php',
			'contact-form-7/wp-contact-form-7.php',
		);

		foreach ( $business_plugins as $plugin ) {
			if ( \is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Determine site type from onboarding data or detection
	 *
	 * @return string The determined site type
	 */
	public static function determine_site_type(): string {
		// First, try to get from onboarding data
		$onboarding_data = \get_option( self::ONBOARDING_SITE_INFO_OPTION, array() );
		$site_type       = $onboarding_data['site_type'] ?? '';

		if ( ! empty( $site_type ) && \array_key_exists( $site_type, self::PLAN_TYPES ) ) {
			return self::PLAN_TYPES[ $site_type ];
		}

		// Check transient for solutions data (for testing compatibility)
		$solutions_data = \get_transient( self::SOLUTIONS_TRANSIENT );
		if ( $solutions_data && isset( $solutions_data['solution'] ) ) {
			$solution = $solutions_data['solution'];
			switch ( $solution ) {
				case 'WP_SOLUTION_COMMERCE':
					return 'ecommerce';
				case 'WP_SOLUTION_SERVICE':
					return 'corporate';
				case 'WP_SOLUTION_CREATOR':
					return 'blog';
			}
		}

		// Fall back to detection
		return self::detect_site_type();
	}

	/**
	 * Create a plan by type
	 *
	 * @param string $plan_type Plan type to create
	 * @param array  $custom_plan_data Custom plan data
	 * @return Plan The created plan
	 */
	public static function create_plan( string $plan_type, array $custom_plan_data = array() ): Plan {

		// ecommerce plan
		if ( 'ecommerce' === $plan_type ) {
			if ( ! class_exists( 'NewfoldLabs\WP\Module\NextSteps\Data\Plans\StorePlan' ) ) {
				require_once NFD_NEXTSTEPS_DIR . '/includes/Data/Plans/StorePlan.php';
			}
			return StorePlan::get_plan();
		}

		// corporate plan
		if ( 'corporate' === $plan_type ) {
			if ( ! class_exists( 'NewfoldLabs\WP\Module\NextSteps\Data\Plans\CorporatePlan' ) ) {
				require_once NFD_NEXTSTEPS_DIR . '/includes/Data/Plans/CorporatePlan.php';
			}
			return CorporatePlan::get_plan();
		}

		// custom plan
		if ( 'custom' === $plan_type && ! empty( $custom_plan_data ) ) {
			return new Plan( $custom_plan_data );
		}

		// if blog type or anything else (blog is default)
		if ( ! class_exists( 'NewfoldLabs\WP\Module\NextSteps\Data\Plans\BlogPlan' ) ) {
			require_once NFD_NEXTSTEPS_DIR . '/includes/Data/Plans/BlogPlan.php';
		}
		return BlogPlan::get_plan();
	}

	/**
	 * Load default plan based on site type
	 *
	 * @return Plan The loaded plan
	 */
	public static function load_default_plan(): Plan {
		$plan_type = self::determine_site_type();
		return self::create_plan( $plan_type );
	}

	/**
	 * Get current plan type
	 *
	 * @return string The current plan type
	 */
	public static function get_current_plan_type(): string {
		$current_plan = PlanRepository::get_current_plan();
		if ( $current_plan ) {
			return $current_plan->type;
		}
		return self::determine_site_type();
	}

	/**
	 * Handle language changes
	 *
	 * @param string $old_value The old language code
	 * @param string $new_value The new language code
	 * @return void
	 */
	public static function on_language_change( $old_value, $new_value ) {
		if ( $old_value === $new_value ) {
			return;
		}
		self::resync_next_steps_data( $new_value, 'site' );
	}

	/**
	 * Handle locale switch
	 *
	 * @param string $locale The new locale code
	 * @return void
	 */
	public static function on_locale_switch( $locale ) {
		self::resync_next_steps_data( $locale, 'locale_switch' );
	}

	/**
	 * Resync next steps data when language changes
	 *
	 * @param string $new_locale The new locale/language code
	 * @param string $change_type The type of change ('site', 'locale_switch')
	 * @return void
	 */
	private static function resync_next_steps_data( $new_locale, $change_type ) {
		// Get the saved plan data (preserves user progress)
		$saved_data      = new Plan( \get_option( PlanRepository::OPTION, array() ) );
		$saved_plan_type = $saved_data->type;

		// Load fresh plan data with new language context
		// We'll create the plan directly based on the saved plan ID
		if ( 'custom' === $saved_plan_type ) {
			// For custom plans, create a new plan with the same structure but updated language
			$new_plan = self::create_plan( $saved_plan_type, $saved_data->to_array() );
		} else {
			$new_plan = self::create_plan( $saved_plan_type );
		}

		if ( $new_plan ) {
			// Use PlanRepository::merge_plan_data to combine saved data with new translations
			// This preserves user progress while updating language content
			$merged_plan = PlanRepository::merge_plan_data( $saved_data, $new_plan );

			// Save the merged plan data
			$saved = PlanRepository::save_plan( $merged_plan );

			if ( $saved ) {
				// Clear any relevant caches
				\wp_cache_delete( 'nfd_next_steps', 'options' );
				// Trigger action for other components that might need to know about the sync
				\do_action( 'nfd_next_steps_language_synced', $new_locale, $change_type, $merged_plan );
			}
		}
	}
}
