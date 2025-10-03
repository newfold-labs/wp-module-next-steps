<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * Class for handling task completion triggers.
 */
class TaskCompletionTriggers {

	/**
	 * Init the Task Completion Triggers
	 *
	 * @param Container $container the container
	 */
	public function __construct( $container ) {
		// Hooks for task completion triggers

		// Add Product Hooks
		// Product creation via REST API
		\add_action( 'woocommerce_rest_insert_product_object', array( __CLASS__, 'on_product_creation' ), 10, 3 );
		// Product creation via post publish (covers admin interface and other methods)
		\add_action( 'publish_product', array( __CLASS__, 'on_product_published' ), 10, 2 );

		// Payment Hooks
		// Payment method configuration - hook into payment gateway settings updates
		\add_action( 'woocommerce_update_options_payment_gateways', array( __CLASS__, 'on_payment_gateway_updated' ), 10 );
		// Also hook into individual payment gateway updates for better coverage
		\add_action( 'init', array( __CLASS__, 'register_payment_gateway_hooks' ), 20 );

	}

	/**
	 * Helper method to mark a task as complete for hooks to use
	 * 
	 * This method will mark a task as complete and save the plan
	 * If the section has multiple tasks, it will mark the task as complete
	 * If the section has one tasks, it will mark the section as complete
	 * 
	 * @param string $track_id The track id
	 * @param string $section_id The section id
	 * @param string $task_id The task id
	 * @return bool True if the task was marked as complete, false otherwise
	 */
	public static function mark_task_as_complete( $track_id, $section_id, $task_id ): bool {
		$current_plan = PlanRepository::get_current_plan(); // Plan object
		if ( $current_plan ) {
			// validate the track section and task exist - optimized single call
			$validtask = $current_plan->has_exact_task( $track_id, $section_id, $task_id );
			if ( $validtask ) {
				// see if section has more tasks, if not, just mark section as complete
				$section = $current_plan->get_section( $track_id, $section_id );
				if ( $section && count( $section->tasks ) === 1 ) {
					$current_plan->update_section_status( $track_id, $section_id, 'done' );
				} else {
					// otherwise mark task as complete
					$current_plan->update_task_status( $track_id, $section_id, $task_id, 'done' );
				}
				// save the plan
				PlanRepository::save_plan( $current_plan );
				return true;
			}
			return false;
		}
	}
	
	/**
	 * Handle product creation via REST API
	 *
	 * @param object $product The product object
	 * @param object $request The request object
	 * @param bool   $creating Whether the product is being created
	 * @return void
	 */
	public static function on_product_creation( $product, $request, $creating ) {
		// Check if WooCommerce is active and loaded
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return;
		}

		if ( $creating ) {
			$current_plan = PlanRepository::get_current_plan();
			if ( $current_plan && 'ecommerce' === $current_plan->type ) {
				// Mark the "Add Products" section and task as complete
				return self::mark_task_as_complete( 'store_build_track', 'setup_products', 'store_add_product' );
			}
		}
	}

	/**
	 * Handle product published via post publish hook
	 * 
	 * This covers product creation through the WordPress admin interface and other methods
	 * that don't go through the REST API
	 *
	 * @param int     $post_id The post ID
	 * @param WP_Post $post The post object
	 * @return void
	 */
	public static function on_product_published( $post_id, $post ) {
		// Only proceed if this is a new product (not an update)
		if ( 'product' === $post->post_type && 'auto-draft' !== $post->post_status ) {
			$current_plan = PlanRepository::get_current_plan();
			if ( $current_plan && 'ecommerce' === $current_plan->type ) {
				// Mark the "Add Products" section and task as complete
				return self::mark_task_as_complete( 'store_build_track', 'setup_products', 'store_add_product' );
			}
		}
	}

	/**
	 * Register hooks for individual payment gateway updates
	 * 
	 * This dynamically registers hooks for all available payment gateways
	 * 
	 * @return void
	 */
	public static function register_payment_gateway_hooks() {
		// Check if WooCommerce is active and loaded
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return;
		}

		// Get all available payment gateways
		$payment_gateways = WC()->payment_gateways();
		if ( ! $payment_gateways ) {
			return;
		}

		$available_gateways = $payment_gateways->payment_gateways();
		
		// Register hooks for each individual payment gateway
		foreach ( $available_gateways as $gateway_id => $gateway ) {
			$hook_name = "woocommerce_update_options_payment_gateways_{$gateway_id}";
			\add_action( $hook_name, array( __CLASS__, 'on_payment_gateway_updated' ), 10 );
		}
	}

	/**
	 * Handle payment gateway settings updated
	 * 
	 * This triggers when any payment gateway settings are updated
	 *
	 * @return void
	 */
	public static function on_payment_gateway_updated() {
		// Check if WooCommerce is active and loaded
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return;
		}

		$current_plan = PlanRepository::get_current_plan();
		if ( $current_plan && 'ecommerce' === $current_plan->type ) {
			// Check if any payment gateways are enabled
			if ( self::has_enabled_payment_gateways() ) {
				// Mark the "Setup Payments" task as complete
				return self::mark_task_as_complete( 'store_build_track', 'setup_payments_shipping', 'store_setup_payments' );
			}
		}
	}

	/**
	 * Check if any payment gateways are enabled
	 * 
	 * @return bool True if at least one payment gateway is enabled, false otherwise
	 */
	private static function has_enabled_payment_gateways(): bool {
		// Check if WooCommerce is active and loaded
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return false;
		}

		// Check if payment gateways are available
		$payment_gateways = WC()->payment_gateways();
		if ( ! $payment_gateways ) {
			return false;
		}

		// Get available payment gateways
		$available_gateways = $payment_gateways->get_available_payment_gateways();
		
		// Check if any gateways are enabled (available gateways are already filtered to enabled ones)
		return ! empty( $available_gateways );
	}

}
