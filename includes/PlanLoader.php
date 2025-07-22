<?php
/**
 * Plan Loader for Next Steps Data.
 *
 * @package WPPluginBluehost
 */

namespace NewfoldLabs\WP\Module\NextSteps;

use function NewfoldLabs\WP\ModuleLoader\container;
use function NewfoldLabs\WP\Context\getContext;

/**
 * NewfoldLabs\WP\Module\NextSteps\PlanLoader
 *
 * Handles plan loading and solution changes for the Next Steps module.
 * All step data is now managed by PlanManager.
 */
class PlanLoader {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Initialize default steps on init
		\add_action( 'init', array( __CLASS__, 'load_default_steps' ), 1 );
		
		// Hook into solution option changes for dynamic plan switching
		\add_action( 'update_option_nfd_module_onboarding_site_info', array( __CLASS__, 'on_sitetype_change' ), 10, 2 );
		
		// Hook into WooCommerce activation to potentially switch to store plan
		// \add_action( 'activated_plugin', array( __CLASS__, 'on_woocommerce_activation' ), 10, 2 );
	}

	/**
	 * Load default steps using PlanManager.
	 * This method is called on init to ensure default steps are loaded.
	 */
	public static function load_default_steps() {
		// Load default steps only if no steps exist
		if ( ! get_option( StepsApi::OPTION ) ) {
			$plan = PlanManager::load_default_plan();
			StepsApi::set_data( $plan->to_array() );
		}
	}

	/**
	 * Handle solution option changes to switch plans dynamically.
	 *
	 * @param mixed $old_value Old solution value
	 * @param array $new_value New solution value
	 */
	public static function on_sitetype_change( $old_value, $new_value ) {
		// Validate new value structure
		if ( ! is_array( $new_value ) || ! isset( $new_value['site_type'] ) ) {
			return;
		}
		
		// Get old site type safely
		$old_site_type = ( is_array( $old_value ) && isset( $old_value['site_type'] ) ) ? $old_value['site_type'] : '';
		$new_site_type = $new_value['site_type'];
		
		// Only switch plan if the solution actually changed
		if ( $old_site_type !== $new_site_type ) {
			// Check if the new site type is a valid plan type
			if ( array_key_exists( $new_site_type, PlanManager::PLAN_TYPES ) ) {
				$plan_type = PlanManager::PLAN_TYPES[ $new_site_type ];
				$plan = PlanManager::switch_plan( $plan_type );
				if ( $plan ) {
					StepsApi::set_data( $plan->to_array() );
				}
			}
		}
	}

	/**
	 * Handle WooCommerce activation to potentially switch to store plan.
	 *
	 * @param string $plugin The plugin being activated.
	 * @param bool   $network_wide Whether the plugin is being activated network-wide.
	 */
	public static function on_woocommerce_activation( $plugin, $network_wide ) {
		// Only for WooCommerce activation
		if ( 'woocommerce/woocommerce.php' === $plugin ) {
			// Switch to store plan when WooCommerce is activated
			$plan = PlanManager::switch_plan( 'ecommerce' );
			if ( $plan ) {
				StepsApi::set_data( $plan->to_array() );
			}
		}
	}
} 