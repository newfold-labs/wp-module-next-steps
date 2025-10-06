<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * Class for handling task completion triggers.
 *
 * Tasks that have smart or automatic completion are managed here.
 * Each task type has:
 * - Task path constants (TASK_PATHS) for single source of truth
 * - Registration method that sets up both hooks and validators
 * - Handler methods that respond to WordPress/plugin events
 * - Validation methods that check existing site state on plan initialization
 *
 * Architecture:
 * - Task paths are defined once in TASK_PATHS constant
 * - Registration methods combine hook and validator setup
 * - Handlers use mark_task_as_complete_by_path() with constants
 * - Validators reuse the same logic as handlers for consistency
 *
 * File Organization:
 * # Constructor & Setup
 * # Product Tasks (WooCommerce)
 * # Payment Tasks (WooCommerce)
 * # Blog Tasks (Content Creation)
 * # Gift Card Tasks
 * # Jetpack Tasks (Performance & Stats)
 * # Yoast Tasks (SEO)
 * # Advanced Reviews Tasks
 * # Affiliates Tasks
 * # Email Templates Tasks
 * # Utility Methods
 */
class TaskCompletionTriggers {

	/**
	 * Task path constants for each task type for easy reference and reuse
	 */
	const TASK_PATHS = array(
		// Product tasks - new post (post type product) created
		'store_add_product'               => 'store_setup.store_build_track.setup_products.store_add_product',

		// Payment tasks - any payment method configured
		'store_setup_payments'            => 'store_setup.store_build_track.setup_payments_shipping.store_setup_payments',

		// Blog tasks - new blog post created
		'blog_first_post'                 => 'blog_setup.blog_build_track.create_content.blog_first_post',

		// Jetpack tasks
		// Boost - Jetpack connected and boost module activated
		'store_improve_performance'       => 'store_setup.store_build_track.store_improve_performance.store_improve_performance',
		'blog_speed_up_site'              => 'blog_setup.blog_grow_track.blog_performance_security.blog_speed_up_site',
		'corporate_install_jetpack_boost' => 'corporate_setup.corporate_grow_track.site_performance_security.corporate_install_jetpack_boost',
		// Stats - jetpack connected and stats module activated
		'blog_connect_jetpack_stats'      => 'blog_setup.blog_brand_track.first_audience_building.blog_connect_jetpack_stats',
		'corporate_setup_jetpack_stats'   => 'corporate_setup.corporate_brand_track.launch_marketing_tools.corporate_setup_jetpack_stats',

		// Yoast tasks - plugin installed
		'store_setup_yoast_premium'       => 'store_setup.store_build_track.next_marketing_steps.store_setup_yoast_premium',
		'blog_install_yoast_premium'      => 'blog_setup.blog_grow_track.content_traffic_strategy.blog_install_yoast_premium',

		// Advanced Reviews tasks - plugin installed
		'store_collect_reviews'           => 'store_setup.store_build_track.store_collect_reviews.store_collect_reviews_task',

		// Affiliate program tasks - pluign installed
		'store_setup_affiliate_program'   => 'store_setup.store_build_track.advanced_social_marketing.store_launch_affiliate',

		// Welcome discount popup - welcome discount popup created
		'store_marketing_welcome_popup'   => 'store_setup.store_build_track.first_marketing_steps.store_marketing_welcome_popup',
		// Gift card tasks - discount product type post created
		'store_create_gift_card'          => 'store_setup.store_build_track.first_marketing_steps.store_create_gift_card',
		// Email templates - plugin installed
		'store_customize_emails'          => 'store_setup.store_build_track.first_marketing_steps.store_customize_emails',
	);

	// ========================================
	// # Constructor & Setup
	// ========================================

	/**
	 * Init the Task Completion Triggers
	 *
	 * @param Container $container the container
	 */
	public function __construct( $container ) {
		// Register all hooks and validators
		$this->register_product_hooks_and_validators();
		$this->register_payment_hooks_and_validators();
		$this->register_blog_hooks_and_validators();
		$this->register_gift_card_hooks_and_validators();
		$this->register_jetpack_hooks_and_validators();
		$this->register_yoast_hooks_and_validators();
		$this->register_advanced_reviews_hooks_and_validators();
		$this->register_affiliates_hooks_and_validators();
		$this->register_email_templates_hooks_and_validators();
	}

	/**
	 * Register hooks and validators for product-related tasks
	 *
	 * @return void
	 */
	private function register_product_hooks_and_validators(): void {
		// Product creation via REST API
		\add_action( 'woocommerce_rest_insert_product_object', array( __CLASS__, 'on_product_creation' ), 10, 3 );
		// Product creation via post publish (covers admin interface and other methods)
		\add_action( 'publish_product', array( __CLASS__, 'on_product_published' ), 10, 2 );

		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_add_product'],
			array( __CLASS__, 'validate_product_creation_state' )
		);
	}

	/**
	 * Register hooks and validators for payment-related tasks
	 *
	 * @return void
	 */
	private function register_payment_hooks_and_validators(): void {

		// Payment method configuration - hook into payment gateway settings updates
		if ( class_exists( '\WC_Payment_Gateways' ) ) {
			$gateways = \WC_Payment_Gateways::instance()->get_payment_gateway_ids();

			foreach ( $gateways as $gateway_id ) {
				add_action( "update_option_woocommerce_{$gateway_id}_settings", array( __CLASS__, 'on_payment_gateway_updated' ) );
			}
		}

		// Also hook into individual payment gateway updates for better coverage
		\add_action( 'init', array( __CLASS__, 'register_payment_gateway_hooks' ), 20 );

		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_setup_payments'],
			array( __CLASS__, 'validate_payment_setup_state' )
		);
	}

	/**
	 * Register hooks and validators for blog-related tasks
	 *
	 * @return void
	 */
	private function register_blog_hooks_and_validators(): void {
		// Blog post creation
		\add_action( 'publish_post', array( __CLASS__, 'on_blog_post_published' ), 10, 2 );

		TaskStateValidator::register_validator(
			self::TASK_PATHS['blog_first_post'],
			array( __CLASS__, 'validate_blog_post_creation_state' )
		);
	}

	/**
	 * Register hooks and validators for gift card-related tasks
	 *
	 * @return void
	 */
	private function register_gift_card_hooks_and_validators(): void {
		// Gift card creation (custom post type)
		\add_action( 'publish_post', array( __CLASS__, 'on_gift_card_published' ), 10, 2 );

		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_create_gift_card'],
			array( __CLASS__, 'validate_gift_card_creation_state' )
		);
	}

	/**
	 * Register hooks and validators for Jetpack-related tasks
	 *
	 * @return void
	 */
	private function register_jetpack_hooks_and_validators(): void {
		// Jetpack connection and Jetpack Boost activation
		\add_action( 'jetpack_site_registered', array( __CLASS__, 'on_jetpack_connected' ), 10 );
		\add_action( 'activated_plugin', array( __CLASS__, 'on_jetpack_boost_activation' ), 10, 2 );
		\add_action( 'jetpack_activate_module', array( __CLASS__, 'on_jetpack_module_activated' ), 10, 1 );
		\add_action( 'jetpack_activate_module_boost', array( __CLASS__, 'on_jetpack_boost_activated' ), 10, 1 );

		// Store plan validators
		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_improve_performance'],
			array( __CLASS__, 'validate_jetpack_performance_state' )
		);

		// Blog plan validators
		TaskStateValidator::register_validator(
			self::TASK_PATHS['blog_speed_up_site'],
			array( __CLASS__, 'validate_jetpack_performance_state' )
		);
		TaskStateValidator::register_validator(
			self::TASK_PATHS['blog_connect_jetpack_stats'],
			array( __CLASS__, 'validate_jetpack_stats_state' )
		);

		// Corporate plan validators
		TaskStateValidator::register_validator(
			self::TASK_PATHS['corporate_install_jetpack_boost'],
			array( __CLASS__, 'validate_jetpack_performance_state' )
		);
		TaskStateValidator::register_validator(
			self::TASK_PATHS['corporate_setup_jetpack_stats'],
			array( __CLASS__, 'validate_jetpack_stats_state' )
		);
	}

	/**
	 * Register hooks and validators for Yoast-related tasks
	 *
	 * @return void
	 */
	private function register_yoast_hooks_and_validators(): void {
		// Yoast SEO Premium activation
		\add_action( 'activated_plugin', array( __CLASS__, 'on_yoast_premium_activation' ), 10, 2 );

		// Store plan validators
		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_setup_yoast_premium'],
			array( __CLASS__, 'validate_yoast_premium_state' )
		);

		// Blog plan validators
		TaskStateValidator::register_validator(
			self::TASK_PATHS['blog_install_yoast_premium'],
			array( __CLASS__, 'validate_yoast_premium_state' )
		);
	}

	/**
	 * Register hooks and validators for Advanced Reviews-related tasks
	 *
	 * @return void
	 */
	private function register_advanced_reviews_hooks_and_validators(): void {
		// Advanced Reviews plugin activation
		\add_action( 'activated_plugin', array( __CLASS__, 'on_advanced_reviews_activation' ), 10, 2 );

		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_collect_reviews'],
			array( __CLASS__, 'validate_advanced_reviews_state' )
		);
	}

	/**
	 * Register hooks and validators for Affiliates-related tasks
	 *
	 * @return void
	 */
	private function register_affiliates_hooks_and_validators(): void {
		// YITH WooCommerce Affiliates plugin activation
		\add_action( 'activated_plugin', array( __CLASS__, 'on_affiliates_activation' ), 10, 2 );

		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_setup_affiliate_program'],
			array( __CLASS__, 'validate_affiliates_state' )
		);
	}

	/**
	 * Register hooks and validators for Email Templates-related tasks
	 *
	 * @return void
	 */
	private function register_email_templates_hooks_and_validators(): void {
		// Email Templates plugin activation
		\add_action( 'activated_plugin', array( __CLASS__, 'on_email_templates_activation' ), 10, 2 );

		TaskStateValidator::register_validator(
			self::TASK_PATHS['store_customize_emails'],
			array( __CLASS__, 'validate_email_templates_state' )
		);
	}


	// ========================================
	// # Product Tasks (WooCommerce)
	// ========================================

	/**
	 * Handle product creation via REST API
	 *
	 * @param object $product  The product object
	 * @param object $request  The request object
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
				return self::mark_task_as_complete_by_path( self::TASK_PATHS['store_add_product'] );
			}
		}
	}

	/**
	 * Validate if products already exist
	 *
	 * @return bool True if products are already created
	 */
	public static function validate_product_creation_state(): bool {
		// Check if WooCommerce is active first
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			return false;
		}

		// Check if any published products exist
		$products = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return ! empty( $products );
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

	// ========================================
	// # Payment Tasks (WooCommerce)
	// ========================================

	/**
	 * Validate if payment setup is already complete
	 *
	 * @return bool True if payment gateways are already configured
	 */
	public static function validate_payment_setup_state(): bool {
		return self::has_enabled_payment_gateways();
	}

	/**
	 * Handle product published via post publish hook
	 *
	 * This covers product creation through the WordPress admin interface and other methods
	 * that don't go through the REST API
	 *
	 * @param int     $post_id The post ID
	 * @param WP_Post $post    The post object
	 * @return bool True if the task was marked as complete, false otherwise
	 */
	public static function on_product_published( $post_id, $post ) {
		// Only proceed if this is a new product (not an update)
		if ( 'product' === $post->post_type && 'auto-draft' !== $post->post_status ) {
			$current_plan = PlanRepository::get_current_plan();
			if ( $current_plan && 'ecommerce' === $current_plan->type ) {
				// Mark the "Add Products" section and task as complete
				return self::mark_task_as_complete_by_path( self::TASK_PATHS['store_add_product'] );
			}
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
				return self::mark_task_as_complete_by_path( self::TASK_PATHS['store_setup_payments'] );
			}
		}
	}

	// ========================================
	// # Blog Tasks (Content Creation)
	// ========================================

	/**
	 * Handle blog post published
	 *
	 * This triggers when a blog post is published
	 *
	 * @param int     $post_id The post ID
	 * @param WP_Post $post    The post object
	 * @return void
	 */
	public static function on_blog_post_published( $post_id, $post ) {
		// Only proceed if this is a published post (not an update from draft)
		if ( 'post' === $post->post_type && 'publish' === $post->post_status ) {
			// Skip the default "Hello World" post
			if ( self::is_hello_world_post( $post ) ) {
				return;
			}

			$current_plan = PlanRepository::get_current_plan();
			if ( $current_plan && 'blog' === $current_plan->type ) {
				// Mark the "Add Your First Blog Post" task as complete
				return self::mark_task_as_complete_by_path( self::TASK_PATHS['blog_first_post'] );
			}
		}
	}

	/**
	 * Validate if a blog post has already been created
	 *
	 * @return bool True if a non-Hello World blog post exists
	 */
	public static function validate_blog_post_creation_state(): bool {
		// Get published posts, excluding the default Hello World post
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 2, // Get a few posts to check
				'fields'         => 'all',
			)
		);

		if ( empty( $posts ) ) {
			return false;
		}

		// Check if any of the posts are NOT the Hello World post
		foreach ( $posts as $post ) {
			if ( ! self::is_hello_world_post( $post ) ) {
				return true; // Found a real blog post
			}
		}

		return false; // Only Hello world post found
	}

	// ========================================
	// # Gift Card Tasks
	// ========================================

	/**
	 * Handle gift card published
	 *
	 * This triggers when a gift card (custom post type) is published
	 *
	 * @param int     $post_id The post ID
	 * @param WP_Post $post    The post object
	 * @return void
	 */
	public static function on_gift_card_published( $post_id, $post ) {
		// Only proceed if this is a published gift card post
		if ( 'bh_gift_card' === $post->post_type && 'publish' === $post->post_status ) {
			$current_plan = PlanRepository::get_current_plan();
			if ( $current_plan && 'ecommerce' === $current_plan->type ) {
				// Mark the "Create Gift Card" task as complete
				return self::mark_task_as_complete_by_path( self::TASK_PATHS['store_create_gift_card'] );
			}
		}
	}

	/**
	 * Validate if a gift card has already been created
	 *
	 * @return bool True if a gift card post exists
	 */
	public static function validate_gift_card_creation_state(): bool {
		// Check if any published gift card posts exist
		$gift_cards = get_posts(
			array(
				'post_type'      => 'bh_gift_card',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		return ! empty( $gift_cards );
	}

	// ========================================
	// # Jetpack Tasks (Performance & Stats)
	// ========================================

	/**
	 * Handle Jetpack connection
	 *
	 * This triggers when Jetpack is successfully connected to WordPress.com
	 *
	 * @return void
	 */
	public static function on_jetpack_connected() {
		$current_plan = PlanRepository::get_current_plan();
		if ( ! $current_plan ) {
			return;
		}

		// Handle different plan types
		switch ( $current_plan->type ) {
			case 'ecommerce':
				// Check if both Jetpack is connected AND Jetpack Boost is active
				if ( self::is_jetpack_performance_ready() ) {
					// Mark the "Improve Performance" task as complete
					self::mark_task_as_complete_by_path( self::TASK_PATHS['store_improve_performance'] );
				}
				break;

			case 'blog':
				// Mark Jetpack Stats connection task as complete
				self::mark_task_as_complete_by_path( self::TASK_PATHS['blog_connect_jetpack_stats'] );

				// Also check if Jetpack Boost is active for performance task
				if ( self::is_jetpack_performance_ready() ) {
					self::mark_task_as_complete_by_path( self::TASK_PATHS['blog_speed_up_site'] );
				}
				break;

			case 'corporate':
				// Mark Jetpack Stats setup task as complete
				self::mark_task_as_complete_by_path( self::TASK_PATHS['corporate_setup_jetpack_stats'] );

				// Also check if Jetpack Boost is active for performance task
				if ( self::is_jetpack_performance_ready() ) {
					self::mark_task_as_complete_by_path( self::TASK_PATHS['corporate_install_jetpack_boost'] );
				}
				break;
		}
	}

	/**
	 * Handle Jetpack Boost activation via plugin activation hook
	 *
	 * @param string $plugin       The plugin name
	 * @param bool   $network_wide Whether the plugin is being activated on the network
	 * @return void
	 */
	public static function on_jetpack_boost_activation( $plugin, $network_wide ) {
		// Check if this is Jetpack Boost being activated
		if ( 'jetpack-boost/jetpack-boost.php' !== $plugin ) {
			return;
		}

		$current_plan = PlanRepository::get_current_plan();
		if ( ! $current_plan ) {
			return;
		}

		// Check if both Jetpack is connected AND Jetpack Boost is now active
		if ( ! self::is_jetpack_performance_ready() ) {
			return;
		}

		// Handle different plan types
		switch ( $current_plan->type ) {
			case 'ecommerce':
				self::mark_task_as_complete_by_path( self::TASK_PATHS['store_improve_performance'] );
				break;

			case 'blog':
				self::mark_task_as_complete_by_path( self::TASK_PATHS['blog_speed_up_site'] );
				break;

			case 'corporate':
				self::mark_task_as_complete_by_path( self::TASK_PATHS['corporate_install_jetpack_boost'] );
				break;
		}
	}

	/**
	 * Handle Jetpack module activation
	 *
	 * This triggers when any Jetpack module is activated, including Boost-related modules
	 *
	 * @param string $module The module name that was activated
	 * @return void
	 */
	public static function on_jetpack_module_activated( $module ) {
		// Check if this is a Boost-related module or performance module
		$boost_modules = array(
			'boost',
			'photon',
			'photon-cdn',
			'lazy-images',
			'minify',
		);

		// Only proceed if it's a performance-related module
		if ( ! in_array( $module, $boost_modules, true ) ) {
			return;
		}

		$current_plan = PlanRepository::get_current_plan();
		if ( $current_plan && 'ecommerce' === $current_plan->type ) {
			// Check if both Jetpack is connected AND Jetpack Boost is active
			if ( self::is_jetpack_performance_ready() ) {
				// Mark the "Improve Performance" task as complete
				return self::mark_task_as_complete_by_path( self::TASK_PATHS['store_improve_performance'] );
			}
		}
	}

	/**
	 * Handle Jetpack boost activation
	 *
	 * This triggers when Jetpack Boost is activated
	 *
	 * @return bool True if the task was marked as complete, false otherwise
	 */
	public static function on_jetpack_boost_activated() {
		$current_plan = PlanRepository::get_current_plan();
		if ( $current_plan && 'ecommerce' === $current_plan->type ) {
			// Check if both Jetpack is connected AND Jetpack Boost is active
			if ( self::is_jetpack_performance_ready() ) {
				// Mark the "Improve Performance" task as complete
				return self::mark_task_as_complete_by_path( self::TASK_PATHS['store_improve_performance'] );
			}
		}
	}

	/**
	 * Validate if Jetpack performance setup is already complete
	 *
	 * @return bool True if Jetpack is connected and Boost is active
	 */
	public static function validate_jetpack_performance_state(): bool {
		return self::is_jetpack_performance_ready();
	}

	/**
	 * Validate if Jetpack Stats is already connected
	 *
	 * @return bool True if Jetpack is connected and Stats module is active
	 */
	public static function validate_jetpack_stats_state(): bool {
		// Check if Jetpack class exists
		if ( ! class_exists( 'Jetpack' ) ) {
			return false;
		}

		// Check if Jetpack is connected
		if ( ! \Jetpack::is_connection_ready() ) {
			return false;
		}

		// Check if Stats module is active (it's usually active by default when connected)
		if ( method_exists( 'Jetpack', 'is_module_active' ) ) {
			return \Jetpack::is_module_active( 'stats' );
		}

		// If we can't check module status but Jetpack is connected, assume stats is available
		return true;
	}

	// ========================================
	// # Yoast Tasks (SEO)
	// ========================================

	/**
	 * Handle Yoast SEO Premium activation
	 *
	 * @param string $plugin       The plugin name
	 * @param bool   $network_wide Whether the plugin is being activated on the network
	 * @return void
	 */
	public static function on_yoast_premium_activation( $plugin, $network_wide ) {
		// Check if this is Yoast SEO Premium being activated
		$yoast_premium_plugins = array(
			'wordpress-seo-premium/wp-seo-premium.php',
			'yoast-seo-premium/wp-seo-premium.php',
			// 'wordpress-seo/wp-seo.php', // to test with free version
		);

		if ( ! in_array( $plugin, $yoast_premium_plugins, true ) ) {
			return;
		}

		$current_plan = PlanRepository::get_current_plan();
		if ( ! $current_plan ) {
			return;
		}

		// Handle different plan types
		switch ( $current_plan->type ) {
			case 'ecommerce':
				self::mark_task_as_complete_by_path( self::TASK_PATHS['store_setup_yoast_premium'] );
				break;

			case 'blog':
				self::mark_task_as_complete_by_path( self::TASK_PATHS['blog_install_yoast_premium'] );
				break;

			// Note: Corporate plan doesn't have a specific Yoast Premium task
			// but has general SEO tasks that could be marked complete
		}
	}

	/**
	 * Validate if Yoast SEO Premium is already active
	 *
	 * @return bool True if Yoast SEO Premium is already active
	 */
	public static function validate_yoast_premium_state(): bool {
		$yoast_premium_plugins = array(
			'wordpress-seo-premium/wp-seo-premium.php',
			'yoast-seo-premium/wp-seo-premium.php',
			// 'wordpress-seo/wp-seo.php', // to test with free version
		);

		foreach ( $yoast_premium_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	// ========================================
	// # Advanced Reviews Tasks
	// ========================================

	/**
	 * Handle Advanced Reviews plugin activation
	 *
	 * @param string $plugin       The plugin name
	 * @param bool   $network_wide Whether the plugin is being activated on the network
	 * @return void
	 */
	public static function on_advanced_reviews_activation( $plugin, $network_wide ) {
		// Check if this is Advanced Reviews being activated
		if ( 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php' !== $plugin ) {
			return;
		}

		$current_plan = PlanRepository::get_current_plan();
		if ( ! $current_plan ) {
			return;
		}

		// Only mark complete for store/ecommerce plan
		if ( 'ecommerce' === $current_plan->type ) {
			self::mark_task_as_complete_by_path( self::TASK_PATHS['store_collect_reviews'] );
		}
	}

	/**
	 * Validate if Advanced Reviews plugin is already active
	 *
	 * @return bool True if Advanced Reviews is already active
	 */
	public static function validate_advanced_reviews_state(): bool {
		return is_plugin_active( 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php' );
	}

	// ========================================
	// # Affiliates Tasks
	// ========================================

	/**
	 * Handle YITH WooCommerce Affiliates plugin activation
	 *
	 * @param string $plugin       The plugin name
	 * @param bool   $network_wide Whether the plugin is being activated on the network
	 * @return void
	 */
	public static function on_affiliates_activation( $plugin, $network_wide ) {
		// Check if this is YITH WooCommerce Affiliates being activated
		if ( 'yith-woocommerce-affiliates/init.php' !== $plugin ) {
			return;
		}

		$current_plan = PlanRepository::get_current_plan();
		if ( ! $current_plan ) {
			return;
		}

		// Only mark complete for store/ecommerce plan
		if ( 'ecommerce' === $current_plan->type ) {
			self::mark_task_as_complete_by_path( self::TASK_PATHS['store_setup_affiliate_program'] );
		}
	}

	/**
	 * Validate if YITH WooCommerce Affiliates plugin is already active
	 *
	 * @return bool True if Affiliates plugin is already active
	 */
	public static function validate_affiliates_state(): bool {
		return is_plugin_active( 'yith-woocommerce-affiliates/init.php' );
	}

	// ========================================
	// # Email Templates Tasks
	// ========================================

	/**
	 * Handle Email Templates plugin activation
	 *
	 * @param string $plugin       The plugin name
	 * @param bool   $network_wide Whether the plugin is being activated on the network
	 * @return void
	 */
	public static function on_email_templates_activation( $plugin, $network_wide ) {
		// Check if this is Email Templates being activated
		if ( 'wp-plugin-email-templates/wp-plugin-email-templates.php' !== $plugin ) {
			return;
		}

		$current_plan = PlanRepository::get_current_plan();
		if ( ! $current_plan ) {
			return;
		}

		// Only mark complete for store/ecommerce plan
		if ( 'ecommerce' === $current_plan->type ) {
			self::mark_task_as_complete_by_path( self::TASK_PATHS['store_customize_emails'] );
		}
	}

	/**
	 * Validate if Email Templates plugin is already active
	 *
	 * @return bool True if Email Templates is already active
	 */
	public static function validate_email_templates_state(): bool {
		return is_plugin_active( 'wp-plugin-email-templates/wp-plugin-email-templates.php' );
	}

	// ========================================
	// # Utility Methods
	// ========================================

	/**
	 * Helper method to mark a task as complete using task path
	 *
	 * @param string $task_path The full task path (plan_id.track_id.section_id.task_id)
	 * @return bool True if the task was marked as complete, false otherwise
	 */
	public static function mark_task_as_complete_by_path( string $task_path ): bool {
		$parts = explode( '.', $task_path );
		if ( count( $parts ) !== 4 ) {
			return false;
		}

		list( $plan_id, $track_id, $section_id, $task_id ) = $parts;
		return self::mark_task_as_complete( $track_id, $section_id, $task_id );
	}

	/**
	 * Helper method to mark a task as complete for hooks to use
	 *
	 * This method will mark a task as complete and save the plan
	 * If the section has multiple tasks, it will mark the task as complete
	 * If the section has one tasks, it will mark the section as complete
	 *
	 * @param string $track_id   The track id
	 * @param string $section_id The section id
	 * @param string $task_id    The task id
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

	/**
	 * Check if Jetpack performance setup is ready
	 *
	 * Validates that both Jetpack is connected and Jetpack Boost is active
	 *
	 * @return bool True if both conditions are met, false otherwise
	 */
	private static function is_jetpack_performance_ready(): bool {
		// Check if Jetpack is connected
		$jetpack_connected = false;
		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'is_connection_ready' ) ) {
			$jetpack_connected = \Jetpack::is_connection_ready();
		} elseif ( function_exists( 'jetpack_is_connected' ) ) {
			$jetpack_connected = jetpack_is_connected();
		} elseif ( class_exists( 'Jetpack_Options' ) && method_exists( 'Jetpack_Options', 'get_option' ) ) {
			// Fallback: check if Jetpack has connection data
			$jetpack_connected = ! empty( \Jetpack_Options::get_option( 'id' ) );
		}

		// Check if Jetpack Boost is active
		$jetpack_boost_active = is_plugin_active( 'jetpack-boost/jetpack-boost.php' ) || class_exists( 'Automattic\Jetpack_Boost\Jetpack_Boost' );

		return $jetpack_connected && $jetpack_boost_active;
	}

	/**
	 * Check if a post is the default "Hello World" post
	 *
	 * @param WP_Post $post The post object
	 * @return bool True if this is the default Hello World post
	 */
	private static function is_hello_world_post( $post ): bool {
		// Check post title
		if ( false !== stripos( $post->post_title, 'Hello world!' ) ) {
			return true;
		}
		// Check post slug
		if ( 'hello-world' === $post->post_name ) {
			return true;
		}
		return false;
	}
}
