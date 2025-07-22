<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Track;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;

/**
 * Plan Manager
 * 
 * Handles plan loading, switching, and management based on nfd_solution option
 */
class PlanManager {

	/**
	 * Option name where the current plan is stored
	 */
	const OPTION = 'nfd_next_steps';

	/**
	 * Option name for the solution type
	 */
	const SOLUTION_OPTION = 'nfd_solution';

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
	 * Get the current plan
	 *
	 * @return Plan|null
	 */
	public static function get_current_plan(): ?Plan {
		$plan_data = get_option( self::OPTION, array() );
		// $plan_data = false; // for resetting data while debugging
		if ( empty( $plan_data ) ) {
			// Load default plan based on solution
			return self::load_default_plan();
		}

		return Plan::from_array( $plan_data );
	}

	/**
	 * Save the current plan
	 *
	 * @param Plan $plan Plan to save
	 * @return bool
	 */
	public static function save_plan( Plan $plan ): bool {
		return update_option( self::OPTION, $plan->to_array() );
	}

	/**
	 * Load default plan based on site type determination
	 *
	 * @return Plan
	 */
	public static function load_default_plan(): Plan {
		$plan_type = self::determine_site_type();
		
		switch ( $plan_type ) {
			case 'blog':
				$plan = self::get_blog_plan();
				break;
			case 'corporate':
				$plan = self::get_corporate_plan();
				break;
			case 'ecommerce':
			default:
				$plan = self::get_ecommerce_plan();
				break;
		}

		// Save the loaded plan
		self::save_plan( $plan );
		
		return $plan;
	}

	/**
	 * Determine the appropriate site type/plan based on multiple data sources
	 * 
	 * Priority order:
	 * 1. nfd_module_onboarding_site_info option (from onboarding)
	 * 2. newfold_solutions transient (from solutions API)  
	 * 3. Legacy solution option (for backward compatibility)
	 * 4. Intelligent site detection (fallback)
	 *
	 * @return string The determined plan type (blog, corporate, ecommerce)
	 */
	public static function determine_site_type(): string {
		// 1. Check onboarding site info first (highest priority)
		$onboarding_info = get_option( 'nfd_module_onboarding_site_info', false );
		if ( is_array( $onboarding_info ) && isset( $onboarding_info['site_type'] ) ) {
			$site_type = $onboarding_info['site_type'];
			if ( array_key_exists( $site_type, self::PLAN_TYPES ) ) {
				return self::PLAN_TYPES[ $site_type ];
			}
		}

		// 2. Check solutions transient (second priority)
		$solutions_data = get_transient( 'newfold_solutions' );
		if ( is_array( $solutions_data ) && isset( $solutions_data['solution'] ) ) {
			$solution = $solutions_data['solution'];
			switch ( $solution ) {
				case 'WP_SOLUTION_COMMERCE':
					return 'ecommerce';
				case 'WP_SOLUTION_CREATOR':
					return 'blog';
				case 'WP_SOLUTION_SERVICE':
					return 'corporate';
			}
		}

		// 3. Check legacy solution option (for backward compatibility)
		$legacy_solution = get_option( self::SOLUTION_OPTION, false );
		if ( false !== $legacy_solution && in_array( $legacy_solution, array( 'blog', 'corporate', 'ecommerce' ), true ) ) {
			return $legacy_solution;
		}

		// 4. Fall back to intelligent detection (from PlanLoader)
		return PlanLoader::detect_site_type();
	}

	/**
	 * Switch to a different plan type
	 *
	 * @param string $plan_type Plan type to switch to
	 * @return Plan|false
	 */
	public static function switch_plan( string $plan_type ) {
		if ( ! in_array( $plan_type, array_values( self::PLAN_TYPES ), true ) && ! in_array( $plan_type, array_keys( self::PLAN_TYPES ), true ) ) {
			return false;
		}

		// If we received an onboarding site_type, convert it to internal plan type
		if ( array_key_exists( $plan_type, self::PLAN_TYPES ) ) {
			$plan_type = self::PLAN_TYPES[ $plan_type ];
		}

		// Clear current plan to force reload
		// delete_option( self::OPTION );
		
		// Load the appropriate plan directly
		switch ( $plan_type ) {
			case 'blog':
				$plan = self::get_blog_plan();
				break;
			case 'corporate':
				$plan = self::get_corporate_plan();
				break;
			case 'ecommerce':
			default:
				$plan = self::get_ecommerce_plan();
				break;
		}

		// Save the loaded plan
		self::save_plan( $plan );
		
		return $plan;
	}

	/**
	 * Get ecommerce plan
	 *
	 * @return Plan
	 */
	public static function get_ecommerce_plan(): Plan {
		return new Plan( array(
			'id'          => 'store_setup',
			'label'       => __( 'Store Setup', 'wp-module-next-steps' ),
			'description' => __( 'Complete your ecommerce store setup with these essential steps:', 'wp-module-next-steps' ),
			'tracks'      => array(
				array(
					'id'       => 'store_build_track',
					'label'    => __( 'Build', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'basic_store_setup',
							'label'       => __( 'Basic Store Setup', 'wp-module-next-steps' ),
							'description' => __( 'Get your store foundation ready', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'              => 'store_quick_setup',
									'title'           => __( 'Quick Setup', 'wp-module-next-steps' ),
									'description'     => __( 'Complete the basic store configuration and settings', 'wp-module-next-steps' ),
									'href'            => '#store-setup-quick-flow-modal',
									'status'          => 'new',
									'priority'        => 1,
									'source'          => 'wp-module-next-steps',
									'data_attributes' => array(
										'data-test-id' => 'store_quick_setup',
										'data-nfd-id'  => 'store_quick_start',
									),
								),
							),
						),
						array(
							'id'          => 'customize_store',
							'label'       => __( 'Customize Your Store', 'wp-module-next-steps' ),
							'description' => __( 'Brand your store to match your business', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_upload_logo',
									'title'       => __( 'Upload Logo', 'wp-module-next-steps' ),
									'description' => __( 'Add your business logo to build brand recognition', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/site-editor.php?',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_choose_colors_fonts',
									'title'       => __( 'Choose Colors and Fonts', 'wp-module-next-steps' ),
									'description' => __( 'Select colors and typography that reflect your brand', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/site-editor.php?p=%2Fstyles',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_header',
									'title'       => __( 'Customize Header', 'wp-module-next-steps' ),
									'description' => __( 'Design your header layout and navigation', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpage&canvas=edit',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_footer',
									'title'       => __( 'Customize Footer', 'wp-module-next-steps' ),
									'description' => __( 'Set up your footer with important links and information', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/site-editor.php?p=%2F&canvas=edit',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_homepage',
									'title'       => __( 'Customize Homepage', 'wp-module-next-steps' ),
									'description' => __( 'Create an engaging homepage that showcases your products', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/site-editor.php?p=%2F&canvas=edit',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'setup_products',
							'label'       => __( 'Set Up Shopping Experience', 'wp-module-next-steps' ),
							'description' => __( 'Add and configure your product catalog', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_add_product',
									'title'       => __( 'Add a Product', 'wp-module-next-steps' ),
									'description' => __( 'Create your first product listing with images and details', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=product',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_product_page',
									'title'       => __( 'Customize the Product Page', 'wp-module-next-steps' ),
									'description' => __( 'Design how individual products are displayed', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post.php?post=',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_shop_page',
									'title'       => __( 'Customize the Shop Page', 'wp-module-next-steps' ),
									'description' => __( 'Configure your main product catalog display', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post.php?post=',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_cart_page',
									'title'       => __( 'Customize the Cart Page', 'wp-module-next-steps' ),
									'description' => __( 'Optimize the shopping cart experience', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post.php?post=',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_checkout_flow',
									'title'       => __( 'Customize the Checkout Flow', 'wp-module-next-steps' ),
									'description' => __( 'Streamline the checkout process for better conversions', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post.php?post=',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'setup_payments_shipping',
							'label'       => __( 'Set Up Payments and Shipping', 'wp-module-next-steps' ),
							'description' => __( 'Configure payment methods and shipping options', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_setup_payments',
									'title'       => __( 'Set Up Payments', 'wp-module-next-steps' ),
									'description' => __( 'Configure payment gateways like PayPal and Stripe', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=payments',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_setup_shipping',
									'title'       => __( 'Set Up Shipping', 'wp-module-next-steps' ),
									'description' => __( 'Define shipping zones, rates, and delivery options', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=shipping',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_setup_taxes',
									'title'       => __( 'Set Up Taxes', 'wp-module-next-steps' ),
									'description' => __( 'Configure tax rates and calculations for your region', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=tax',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'setup_legal_pages',
							'label'       => __( 'Set Up Legal Pages', 'wp-module-next-steps' ),
							'description' => __( 'Create essential legal pages for compliance', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_privacy_policy',
									'title'       => __( 'Privacy Policy', 'wp-module-next-steps' ),
									'description' => __( 'Create a privacy policy to comply with data protection laws', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/privacy.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_terms_conditions',
									'title'       => __( 'Terms & Conditions', 'wp-module-next-steps' ),
									'description' => __( 'Set terms of service for customer purchases', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_return_refund_policy',
									'title'       => __( 'Return and Refund Policy', 'wp-module-next-steps' ),
									'description' => __( 'Define your return and refund procedures', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php?post_type=page',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
				array(
					'id'       => 'store_brand_track',
					'label'    => __( 'Brand', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'first_marketing_steps',
							'label'       => __( 'First Marketing Steps', 'wp-module-next-steps' ),
							'description' => __( 'Set up essential marketing tools and features', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_enable_social_login',
									'title'       => __( 'Enable Social Login Register for Your Customers', 'wp-module-next-steps' ),
									'description' => __( 'Allow customers to register and login using social media accounts', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=account',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_configure_welcome_popup',
									'title'       => __( 'Configure Welcome Discount Popup', 'wp-module-next-steps' ),
									'description' => __( 'Create a popup to capture emails and offer discounts', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_create_gift_card',
									'title'       => __( 'Create a Gift Card to Sell in Your Shop', 'wp-module-next-steps' ),
									'description' => __( 'Add gift cards as a product option to increase sales', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=product',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_enable_abandoned_cart',
									'title'       => __( 'Enable Abandoned Cart Emails', 'wp-module-next-steps' ),
									'description' => __( 'Recover lost sales with automated cart abandonment emails', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_emails',
									'title'       => __( 'Customize Your Store Emails', 'wp-module-next-steps' ),
									'description' => __( 'Brand your order confirmation and customer emails', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=email',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_add_google_analytics',
									'title'       => __( 'Add Google Analytics', 'wp-module-next-steps' ),
									'description' => __( 'Track visitor behavior and store performance', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 6,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'social_media_engagement',
							'label'       => __( 'Launch and Promote - Social Media Setup & Engagement', 'wp-module-next-steps' ),
							'description' => __( 'Connect your store to social media platforms', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_connect_facebook',
									'title'       => __( 'Connect Facebook Store', 'wp-module-next-steps' ),
									'description' => __( 'Sync your products with Facebook Shop', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_connect_instagram',
									'title'       => __( 'Connect Instagram Shopping', 'wp-module-next-steps' ),
									'description' => __( 'Enable product tagging on Instagram posts', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_connect_tiktok',
									'title'       => __( 'Connect TikTok Shop', 'wp-module-next-steps' ),
									'description' => __( 'Integrate with TikTok for product promotion', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_add_social_sharing',
									'title'       => __( 'Add Social Sharing Buttons', 'wp-module-next-steps' ),
									'description' => __( 'Let customers share products on social media', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_add_social_feed',
									'title'       => __( 'Add Social Media Feed to Homepage', 'wp-module-next-steps' ),
									'description' => __( 'Display your social media posts on your homepage', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'seo_visibility',
							'label'       => __( 'Launch and Promote - SEO & Store Visibility', 'wp-module-next-steps' ),
							'description' => __( 'Optimize your store for search engines', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_optimize_seo',
									'title'       => __( 'Optimize Your Store SEO', 'wp-module-next-steps' ),
									'description' => __( 'Improve search engine rankings with SEO best practices', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_submit_search_console',
									'title'       => __( 'Submit Site to Google Search Console', 'wp-module-next-steps' ),
									'description' => __( 'Monitor your site\'s search performance', 'wp-module-next-steps' ),
									'href'        => 'https://search.google.com/search-console',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_create_sitemap',
									'title'       => __( 'Create a Custom Sitemap', 'wp-module-next-steps' ),
									'description' => __( 'Help search engines discover your products', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_tools',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
				array(
					'id'       => 'store_grow_track',
					'label'    => __( 'Grow', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'improve_customer_experience',
							'label'       => __( 'Improve Your Customer Experience to Sell More', 'wp-module-next-steps' ),
							'description' => __( 'Enhance customer satisfaction and increase sales', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_customize_thankyou',
									'title'       => __( 'Customize the Thank You Page', 'wp-module-next-steps' ),
									'description' => __( 'Create a branded post-purchase experience', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=checkout',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_customize_account',
									'title'       => __( 'Customize Your Customer\'s Account Page', 'wp-module-next-steps' ),
									'description' => __( 'Improve the customer dashboard experience', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=account',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_collect_reviews',
									'title'       => __( 'Collect and Show Reviews for Your Products', 'wp-module-next-steps' ),
									'description' => __( 'Build trust with customer reviews and testimonials', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=products',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'advanced_social_marketing',
							'label'       => __( 'Advanced Social & Influencer Marketing', 'wp-module-next-steps' ),
							'description' => __( 'Implement advanced marketing strategies', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_launch_affiliate',
									'title'       => __( 'Launch an Affiliate Program', 'wp-module-next-steps' ),
									'description' => __( 'Let others promote your products for commission', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_create_rewards',
									'title'       => __( 'Create a Points & Rewards Program for Your Customers', 'wp-module-next-steps' ),
									'description' => __( 'Encourage repeat purchases with loyalty rewards', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_run_first_ad',
									'title'       => __( 'Run First Facebook or Instagram Ad', 'wp-module-next-steps' ),
									'description' => __( 'Start paid advertising to reach more customers', 'wp-module-next-steps' ),
									'href'        => 'https://www.facebook.com/business/ads',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_launch_giveaway',
									'title'       => __( 'Launch Product Giveaway Campaign', 'wp-module-next-steps' ),
									'description' => __( 'Generate buzz and attract new customers', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_create_influencer_list',
									'title'       => __( 'Create Influencer Outreach List', 'wp-module-next-steps' ),
									'description' => __( 'Build relationships with influencers in your niche', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_track_utm_campaigns',
									'title'       => __( 'Track UTM Campaign Links', 'wp-module-next-steps' ),
									'description' => __( 'Monitor the effectiveness of your marketing campaigns', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 6,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'next_marketing_steps',
							'label'       => __( 'Next Marketing Steps', 'wp-module-next-steps' ),
							'description' => __( 'Advanced marketing tactics to grow your business', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_write_blog_post',
									'title'       => __( 'Write a Blog Post', 'wp-module-next-steps' ),
									'description' => __( 'Create content to drive traffic and improve SEO', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_create_sale_campaign',
									'title'       => __( 'Create a Sale & Promo Campaign for Your Products', 'wp-module-next-steps' ),
									'description' => __( 'Run promotional campaigns to boost sales', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wc-settings&tab=general',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_create_upsell',
									'title'       => __( 'Create an Upsell Campaign', 'wp-module-next-steps' ),
									'description' => __( 'Increase average order value with strategic upsells', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_setup_yoast_premium',
									'title'       => __( 'Setup Yoast Premium to Drive Traffic to Your Store', 'wp-module-next-steps' ),
									'description' => __( 'Advanced SEO features to improve search rankings', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'performance_security',
							'label'       => __( 'Performance & Security', 'wp-module-next-steps' ),
							'description' => __( 'Optimize your store for speed and security', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_improve_performance',
									'title'       => __( 'Improve Performance and Speed with Jetpack Boost', 'wp-module-next-steps' ),
									'description' => __( 'Speed up your store for better user experience', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack-boost',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_enable_auto_backup',
									'title'       => __( 'Enable Auto-Backup & Update Alerts', 'wp-module-next-steps' ),
									'description' => __( 'Protect your store with automatic backups', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/security',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_create_staging',
									'title'       => __( 'Create a Staging Website', 'wp-module-next-steps' ),
									'description' => __( 'Test changes safely before going live', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/tools',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'store_analysis',
							'label'       => __( 'Store Analysis', 'wp-module-next-steps' ),
							'description' => __( 'Monitor and analyze your store performance', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'store_monitor_traffic',
									'title'       => __( 'Monitor Traffic and Conversion Rates', 'wp-module-next-steps' ),
									'description' => __( 'Track key metrics to optimize your store', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_run_ab_test',
									'title'       => __( 'Run A/B Test on Homepage Banner', 'wp-module-next-steps' ),
									'description' => __( 'Test different designs to improve conversions', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'store_review_performance',
									'title'       => __( 'Review Monthly Performance Dashboard', 'wp-module-next-steps' ),
									'description' => __( 'Analyze monthly reports to identify trends', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
			),
		) );
	}

	/**
	 * Get blog plan
	 *
	 * @return Plan
	 */
	public static function get_blog_plan(): Plan {
		return new Plan( array(
			'id'          => 'blog_setup',
			'label'       => __( 'Blog Setup', 'wp-module-next-steps' ),
			'description' => __( 'Get your blog up and running with these essential steps:', 'wp-module-next-steps' ),
			'tracks'      => array(
				array(
					'id'       => 'blog_build_track',
					'label'    => __( 'Build', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'basic_blog_setup',
							'label'       => __( 'Basic Blog Setup', 'wp-module-next-steps' ),
							'description' => __( 'Configure your blog\'s basic settings', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_set_site_title',
									'title'       => __( 'Set Site Title', 'wp-module-next-steps' ),
									'description' => __( 'Choose a memorable title for your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_set_tagline',
									'title'       => __( 'Set Tagline', 'wp-module-next-steps' ),
									'description' => __( 'Create a compelling tagline that describes your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_set_timezone',
									'title'       => __( 'Set Time Zone', 'wp-module-next-steps' ),
									'description' => __( 'Configure your blog\'s timezone for accurate posting', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_set_language',
									'title'       => __( 'Set Language', 'wp-module-next-steps' ),
									'description' => __( 'Choose your blog\'s primary language', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'customize_blog',
							'label'       => __( 'Customize Your Blog', 'wp-module-next-steps' ),
							'description' => __( 'Make your blog visually appealing and unique', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_upload_logo',
									'title'       => __( 'Upload Logo', 'wp-module-next-steps' ),
									'description' => __( 'Add your blog logo to establish brand identity', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=title_tagline',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_choose_colors_fonts',
									'title'       => __( 'Choose Colors and Fonts', 'wp-module-next-steps' ),
									'description' => __( 'Select colors and typography that match your style', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=colors',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_customize_header_footer',
									'title'       => __( 'Customize Header and Footer', 'wp-module-next-steps' ),
									'description' => __( 'Design your header and footer layout', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_customize_homepage',
									'title'       => __( 'Customize Homepage', 'wp-module-next-steps' ),
									'description' => __( 'Choose a layout or blog grid for your homepage', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=static_front_page',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'create_content',
							'label'       => __( 'Create Content', 'wp-module-next-steps' ),
							'description' => __( 'Start creating engaging blog content', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_first_post',
									'title'       => __( 'Add Your First Blog Post', 'wp-module-next-steps' ),
									'description' => __( 'Write and publish your first blog post', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_about_page',
									'title'       => __( 'Create an "About" Page', 'wp-module-next-steps' ),
									'description' => __( 'Tell your readers about yourself and your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_set_featured_image',
									'title'       => __( 'Set a Featured Image for One Post', 'wp-module-next-steps' ),
									'description' => __( 'Add visual appeal to your posts with featured images', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'setup_navigation',
							'label'       => __( 'Set Up Navigation', 'wp-module-next-steps' ),
							'description' => __( 'Create user-friendly navigation for your blog', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_add_pages',
									'title'       => __( 'Add Pages for Home, Blog, About, Contact', 'wp-module-next-steps' ),
									'description' => __( 'Create essential pages for your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php?post_type=page',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_create_primary_menu',
									'title'       => __( 'Create a Primary Menu', 'wp-module-next-steps' ),
									'description' => __( 'Set up main navigation for your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/nav-menus.php',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_create_footer_menu',
									'title'       => __( 'Create a Footer Menu', 'wp-module-next-steps' ),
									'description' => __( 'Add footer navigation with important links', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/nav-menus.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'setup_essential_pages',
							'label'       => __( 'Set Up Essential Pages', 'wp-module-next-steps' ),
							'description' => __( 'Create important legal and policy pages', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_privacy_policy',
									'title'       => __( 'Add a Privacy Policy', 'wp-module-next-steps' ),
									'description' => __( 'Create a privacy policy for legal compliance', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/privacy.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_terms_conditions',
									'title'       => __( 'Add Terms & Conditions', 'wp-module-next-steps' ),
									'description' => __( 'Set terms of use for your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_accessibility_statement',
									'title'       => __( 'Add an Accessibility Statement', 'wp-module-next-steps' ),
									'description' => __( 'Show your commitment to accessibility', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
				array(
					'id'       => 'blog_brand_track',
					'label'    => __( 'Brand', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'first_audience_building',
							'label'       => __( 'First Audience-Building Steps', 'wp-module-next-steps' ),
							'description' => __( 'Start building your blog audience', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_enable_social_login',
									'title'       => __( 'Enable Social Login', 'wp-module-next-steps' ),
									'description' => __( 'Allow readers to comment using social media accounts', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-discussion.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_welcome_subscribe_popup',
									'title'       => __( 'Add a Welcome-Subscribe Popup', 'wp-module-next-steps' ),
									'description' => __( 'Convert visitors to your email list with a popup', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_customize_notification_emails',
									'title'       => __( 'Customize Notification Emails', 'wp-module-next-steps' ),
									'description' => __( 'Personalize comment replies and new-post alerts', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-discussion.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_connect_jetpack_stats',
									'title'       => __( 'Connect Jetpack Stats (or Google Analytics 4)', 'wp-module-next-steps' ),
									'description' => __( 'Track your blog\'s performance and visitor behavior', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'test_everything',
							'label'       => __( 'Test Everything', 'wp-module-next-steps' ),
							'description' => __( 'Ensure your blog works perfectly before launch', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_check_mobile_optimization',
									'title'       => __( 'Check Mobile Optimization', 'wp-module-next-steps' ),
									'description' => __( 'Test how your blog looks on mobile devices', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?device=mobile',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_test_navigation',
									'title'       => __( 'Click Through Every Menu Item & Post', 'wp-module-next-steps' ),
									'description' => __( 'Test all links and navigation to ensure they work', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_disable_coming_soon',
									'title'       => __( 'Disable "Coming Soon" Mode When Ready', 'wp-module-next-steps' ),
									'description' => __( 'Make your blog visible to the public', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/home',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'launch_promote_social',
							'label'       => __( 'Launch & Promote - Social Presence', 'wp-module-next-steps' ),
							'description' => __( 'Establish your blog\'s social media presence', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_connect_facebook',
									'title'       => __( 'Connect Facebook Page Auto-Sharing', 'wp-module-next-steps' ),
									'description' => __( 'Automatically share new posts to Facebook', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_connect_instagram',
									'title'       => __( 'Connect Instagram Auto-Sharing', 'wp-module-next-steps' ),
									'description' => __( 'Share your blog posts on Instagram', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_connect_tiktok',
									'title'       => __( 'Connect TikTok Profile', 'wp-module-next-steps' ),
									'description' => __( 'Link your TikTok profile to your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_add_social_sharing',
									'title'       => __( 'Add Social-Sharing Buttons to Posts', 'wp-module-next-steps' ),
									'description' => __( 'Let readers share your content on social media', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_embed_social_feed',
									'title'       => __( 'Embed a Social Media Feed on Homepage', 'wp-module-next-steps' ),
									'description' => __( 'Show your latest social media posts on your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'launch_promote_seo',
							'label'       => __( 'Launch & Promote - SEO & Visibility', 'wp-module-next-steps' ),
							'description' => __( 'Optimize your blog for search engines', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_optimize_seo',
									'title'       => __( 'Optimize On-Page SEO', 'wp-module-next-steps' ),
									'description' => __( 'Use Yoast or All-in-One SEO to optimize your content', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_submit_search_console',
									'title'       => __( 'Submit Site to Google Search Console', 'wp-module-next-steps' ),
									'description' => __( 'Monitor your blog\'s search performance', 'wp-module-next-steps' ),
									'href'        => 'https://search.google.com/search-console',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_generate_sitemap',
									'title'       => __( 'Generate & Submit XML Sitemap', 'wp-module-next-steps' ),
									'description' => __( 'Help search engines discover your content', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_tools',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
				array(
					'id'       => 'blog_grow_track',
					'label'    => __( 'Grow', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'enhance_reader_experience',
							'label'       => __( 'Enhance Reader Experience', 'wp-module-next-steps' ),
							'description' => __( 'Improve engagement and user experience', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_enable_comments',
									'title'       => __( 'Enable & Style Comments Section', 'wp-module-next-steps' ),
									'description' => __( 'Set up Akismet or Antispam Bee for comment moderation', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-discussion.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_customize_author_boxes',
									'title'       => __( 'Customize Author/Profile Boxes', 'wp-module-next-steps' ),
									'description' => __( 'Create professional author profiles', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/profile.php',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_display_testimonials',
									'title'       => __( 'Display Testimonials or Highlighted Comments', 'wp-module-next-steps' ),
									'description' => __( 'Showcase positive feedback from readers', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_create_favicon',
									'title'       => __( 'Create a Favicon', 'wp-module-next-steps' ),
									'description' => __( 'Add a browser icon for your blog', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=title_tagline',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'advanced_promotion_partnerships',
							'label'       => __( 'Advanced Promotion & Partnerships', 'wp-module-next-steps' ),
							'description' => __( 'Expand your blog\'s reach and partnerships', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_build_newsletter',
									'title'       => __( 'Build an Email Newsletter', 'wp-module-next-steps' ),
									'description' => __( 'Create regular email updates for your readers', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_draft_outreach_list',
									'title'       => __( 'Draft an Influencer/Guest-Post Outreach List', 'wp-module-next-steps' ),
									'description' => __( 'Build relationships with other bloggers and influencers', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_run_first_ad',
									'title'       => __( 'Run First Facebook or Instagram Ad', 'wp-module-next-steps' ),
									'description' => __( 'Promote a pillar article with paid advertising', 'wp-module-next-steps' ),
									'href'        => 'https://www.facebook.com/business/ads',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_launch_giveaway',
									'title'       => __( 'Launch a Content Giveaway', 'wp-module-next-steps' ),
									'description' => __( 'Offer a free e-book or resource to grow your audience', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_track_utm_campaigns',
									'title'       => __( 'Track Campaigns with UTM Links', 'wp-module-next-steps' ),
									'description' => __( 'Monitor campaign effectiveness in Analytics', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'content_traffic_strategy',
							'label'       => __( 'Content & Traffic Strategy', 'wp-module-next-steps' ),
							'description' => __( 'Develop advanced content strategies', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_plan_content_series',
									'title'       => __( 'Plan a Content Series or Editorial Calendar', 'wp-module-next-steps' ),
									'description' => __( 'Create a structured content planning approach', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_implement_internal_linking',
									'title'       => __( 'Implement Internal-Linking Strategy', 'wp-module-next-steps' ),
									'description' => __( 'Link older posts to improve SEO and user engagement', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_install_yoast_premium',
									'title'       => __( 'Install Yoast Premium for Advanced Schemas', 'wp-module-next-steps' ),
									'description' => __( 'Enhance SEO with advanced structured data', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'performance_security',
							'label'       => __( 'Performance & Security', 'wp-module-next-steps' ),
							'description' => __( 'Optimize your blog for speed and security', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_speed_up_site',
									'title'       => __( 'Speed-up Site with Jetpack Boost', 'wp-module-next-steps' ),
									'description' => __( 'Improve loading times with caching and optimization', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack-boost',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_enable_auto_backups',
									'title'       => __( 'Enable Automatic Backups & Update Alerts', 'wp-module-next-steps' ),
									'description' => __( 'Protect your blog with automated backups', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/security',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_create_staging_site',
									'title'       => __( 'Create a Staging Site', 'wp-module-next-steps' ),
									'description' => __( 'Test changes safely before publishing', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/tools',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'blog_analytics',
							'label'       => __( 'Blog Analytics', 'wp-module-next-steps' ),
							'description' => __( 'Monitor and analyze your blog performance', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_monitor_traffic',
									'title'       => __( 'Monitor Traffic & Engagement', 'wp-module-next-steps' ),
									'description' => __( 'Track visitor behavior in Jetpack or Google Analytics', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_review_monthly_dashboard',
									'title'       => __( 'Review Monthly Performance Dashboard', 'wp-module-next-steps' ),
									'description' => __( 'Analyze monthly reports and set growth goals', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
			),
		) );
	}

	/**
	 * Get corporate plan
	 *
	 * @return Plan
	 */
	public static function get_corporate_plan(): Plan {
		return new Plan( array(
			'id'          => 'corporate_setup',
			'label'       => __( 'Corporate Setup', 'wp-module-next-steps' ),
			'description' => __( 'Set up your corporate website with these essential steps:', 'wp-module-next-steps' ),
			'tracks'      => array(
				array(
					'id'       => 'corporate_build_track',
					'label'    => __( 'Build', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'basic_site_setup',
							'label'       => __( 'Basic Site Setup', 'wp-module-next-steps' ),
							'description' => __( 'Configure your corporate website foundation', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_complete_basic_setup',
									'title'       => __( 'Complete Basic Site Setup', 'wp-module-next-steps' ),
									'description' => __( 'Configure essential website settings', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_set_site_name',
									'title'       => __( 'Set Site Name', 'wp-module-next-steps' ),
									'description' => __( 'Add your company name as the site title', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_set_tagline',
									'title'       => __( 'Set Tagline', 'wp-module-next-steps' ),
									'description' => __( 'Create a professional tagline that describes your business', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_set_language',
									'title'       => __( 'Set Language', 'wp-module-next-steps' ),
									'description' => __( 'Choose your website\'s primary language', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_set_timezone',
									'title'       => __( 'Set Time Zone', 'wp-module-next-steps' ),
									'description' => __( 'Configure your website\'s timezone', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/options-general.php',
									'status'      => 'new',
									'priority'    => 5,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'customize_website',
							'label'       => __( 'Customize Your Website', 'wp-module-next-steps' ),
							'description' => __( 'Brand your corporate website professionally', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_upload_logo',
									'title'       => __( 'Upload Company Logo', 'wp-module-next-steps' ),
									'description' => __( 'Add your company logo to establish brand identity', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=title_tagline',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_choose_brand_colors',
									'title'       => __( 'Choose Brand Colors and Fonts', 'wp-module-next-steps' ),
									'description' => __( 'Apply your corporate brand colors and typography', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=colors',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_customize_header_footer',
									'title'       => __( 'Customize Header and Footer', 'wp-module-next-steps' ),
									'description' => __( 'Design professional header and footer layouts', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_customize_homepage',
									'title'       => __( 'Customize Homepage Layout', 'wp-module-next-steps' ),
									'description' => __( 'Select a template or blocks for your homepage', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=static_front_page',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'setup_key_pages',
							'label'       => __( 'Set Up Key Pages', 'wp-module-next-steps' ),
							'description' => __( 'Create essential corporate pages', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_create_about_page',
									'title'       => __( 'Create an About Us Page', 'wp-module-next-steps' ),
									'description' => __( 'Tell visitors about your company and mission', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_create_services_page',
									'title'       => __( 'Create a Services or What We Do Page', 'wp-module-next-steps' ),
									'description' => __( 'Showcase your company\'s services and offerings', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_create_contact_page',
									'title'       => __( 'Create a Contact Page', 'wp-module-next-steps' ),
									'description' => __( 'Add contact form and map for customer inquiries', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_add_team_page',
									'title'       => __( 'Add a Team or Leadership Page', 'wp-module-next-steps' ),
									'description' => __( 'Introduce your team members and leadership', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'configure_navigation',
							'label'       => __( 'Configure Navigation', 'wp-module-next-steps' ),
							'description' => __( 'Set up professional website navigation', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_add_navigation_pages',
									'title'       => __( 'Add Pages for Home, Blog, About, Contact', 'wp-module-next-steps' ),
									'description' => __( 'Create all essential navigation pages', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php?post_type=page',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_create_primary_menu',
									'title'       => __( 'Create a Primary Menu', 'wp-module-next-steps' ),
									'description' => __( 'Set up main navigation menu', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/nav-menus.php',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_add_footer_menu',
									'title'       => __( 'Add a Footer Menu', 'wp-module-next-steps' ),
									'description' => __( 'Create footer navigation with Privacy, Terms, etc.', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/nav-menus.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'add_legal_trust_content',
							'label'       => __( 'Add Legal & Trust Content', 'wp-module-next-steps' ),
							'description' => __( 'Build credibility with legal and trust signals', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_privacy_policy',
									'title'       => __( 'Add a Privacy Policy', 'wp-module-next-steps' ),
									'description' => __( 'Create privacy policy for legal compliance', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/privacy.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_terms_conditions',
									'title'       => __( 'Add Terms & Conditions', 'wp-module-next-steps' ),
									'description' => __( 'Set terms of service for your business', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_accessibility_statement',
									'title'       => __( 'Add an Accessibility Statement', 'wp-module-next-steps' ),
									'description' => __( 'Show commitment to web accessibility', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_client_testimonials',
									'title'       => __( 'Add Client Logos or Testimonials', 'wp-module-next-steps' ),
									'description' => __( 'Display client logos and positive testimonials', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
				array(
					'id'       => 'corporate_brand_track',
					'label'    => __( 'Brand', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'establish_brand_online',
							'label'       => __( 'Establish Your Brand Online', 'wp-module-next-steps' ),
							'description' => __( 'Build your corporate online presence', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_setup_custom_domain',
									'title'       => __( 'Set Up a Custom Domain', 'wp-module-next-steps' ),
									'description' => __( 'Configure your professional domain name', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/domains',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_create_favicon',
									'title'       => __( 'Create a Favicon', 'wp-module-next-steps' ),
									'description' => __( 'Add a professional browser icon', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php?autofocus[section]=title_tagline',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_connect_google_business',
									'title'       => __( 'Connect Your Google Business Profile', 'wp-module-next-steps' ),
									'description' => __( 'Enhance local/regional presence with Google Business', 'wp-module-next-steps' ),
									'href'        => 'https://www.google.com/business/',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_create_branded_email',
									'title'       => __( 'Create a Branded Email Address', 'wp-module-next-steps' ),
									'description' => __( 'Set up professional email like info@yourcompany.com', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/email',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'launch_marketing_tools',
							'label'       => __( 'Launch Essential Marketing Tools', 'wp-module-next-steps' ),
							'description' => __( 'Set up analytics and SEO tools', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_setup_jetpack_stats',
									'title'       => __( 'Set Up Jetpack Stats (or GA4)', 'wp-module-next-steps' ),
									'description' => __( 'Track website performance and visitor behavior', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_connect_search_console',
									'title'       => __( 'Connect Google Search Console', 'wp-module-next-steps' ),
									'description' => __( 'Monitor search performance and indexing', 'wp-module-next-steps' ),
									'href'        => 'https://search.google.com/search-console',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_install_seo_plugin',
									'title'       => __( 'Install a SEO Plugin (Yoast Premium)', 'wp-module-next-steps' ),
									'description' => __( 'Optimize your website for search engines', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_add_social_sharing',
									'title'       => __( 'Add Social Sharing Settings', 'wp-module-next-steps' ),
									'description' => __( 'Enable social media sharing for content', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'setup_contact_engagement',
							'label'       => __( 'Set Up Contact & Engagement', 'wp-module-next-steps' ),
							'description' => __( 'Make it easy for customers to contact you', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_add_contact_form',
									'title'       => __( 'Add a Contact Form', 'wp-module-next-steps' ),
									'description' => __( 'Create contact form with email routing', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=formidable',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_embed_map',
									'title'       => __( 'Embed a Map or Location', 'wp-module-next-steps' ),
									'description' => __( 'Show your business location if applicable', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post.php?post={contactPageId}&action=edit',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_add_live_chat',
									'title'       => __( 'Add Live Chat or Contact Widget', 'wp-module-next-steps' ),
									'description' => __( 'Enable real-time customer support', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_link_social_profiles',
									'title'       => __( 'Link to Social Media Profiles', 'wp-module-next-steps' ),
									'description' => __( 'Add social media links in header/footer', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'final_review_before_live',
							'label'       => __( 'Final Review Before Going Live', 'wp-module-next-steps' ),
							'description' => __( 'Test everything before launch', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_preview_devices',
									'title'       => __( 'Preview on Mobile, Tablet, and Desktop', 'wp-module-next-steps' ),
									'description' => __( 'Test responsive design on all devices', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_test_forms_navigation',
									'title'       => __( 'Test Contact Form and Navigation Links', 'wp-module-next-steps' ),
									'description' => __( 'Ensure all forms and links work properly', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_disable_coming_soon',
									'title'       => __( 'Disable "Coming Soon" Mode', 'wp-module-next-steps' ),
									'description' => __( 'Make your website visible to the public', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/home',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_external_review',
									'title'       => __( 'Ask Someone Outside Your Team to Review', 'wp-module-next-steps' ),
									'description' => __( 'Get external feedback before launching', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
				array(
					'id'       => 'corporate_grow_track',
					'label'    => __( 'Grow', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'strengthen_online_presence',
							'label'       => __( 'Strengthen Online Presence', 'wp-module-next-steps' ),
							'description' => __( 'Build credibility and trust', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_add_client_testimonials',
									'title'       => __( 'Add Client Testimonials or Reviews', 'wp-module-next-steps' ),
									'description' => __( 'Showcase positive client feedback', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_add_certifications',
									'title'       => __( 'Add Certifications, Memberships, or Awards', 'wp-module-next-steps' ),
									'description' => __( 'Display industry credentials and achievements', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'build_content_seo_trust',
							'label'       => __( 'Build Content for SEO & Trust', 'wp-module-next-steps' ),
							'description' => __( 'Create valuable content that builds authority', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_publish_first_blog_post',
									'title'       => __( 'Publish Your First Company Blog Post', 'wp-module-next-steps' ),
									'description' => __( 'Share industry insights or company news', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_create_faq_page',
									'title'       => __( 'Create a FAQ Page', 'wp-module-next-steps' ),
									'description' => __( 'Answer common customer questions', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_optimize_key_pages',
									'title'       => __( 'Optimize Your Key Pages for Keywords', 'wp-module-next-steps' ),
									'description' => __( 'Improve SEO and readability of important pages', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php?post_type=page',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_generate_submit_sitemap',
									'title'       => __( 'Generate and Submit XML Sitemap', 'wp-module-next-steps' ),
									'description' => __( 'Help search engines discover your pages', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=wpseo_tools',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'marketing_lead_generation',
							'label'       => __( 'Marketing & Lead Generation', 'wp-module-next-steps' ),
							'description' => __( 'Generate and nurture business leads', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_setup_email_capture',
									'title'       => __( 'Set Up an Email Capture Form', 'wp-module-next-steps' ),
									'description' => __( 'Build a newsletter subscription list', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_connect_crm',
									'title'       => __( 'Connect to CRM or Email Tool', 'wp-module-next-steps' ),
									'description' => __( 'Integrate with Mailchimp, HubSpot, or similar', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_add_cta_section',
									'title'       => __( 'Add a Call-to-Action Section to Homepage', 'wp-module-next-steps' ),
									'description' => __( 'Encourage visitors to take action', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/customize.php',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_create_downloadable_resource',
									'title'       => __( 'Create a Downloadable Resource', 'wp-module-next-steps' ),
									'description' => __( 'Offer brochure, whitepaper, or guide for lead generation', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'site_performance_security',
							'label'       => __( 'Site Performance & Security', 'wp-module-next-steps' ),
							'description' => __( 'Optimize and secure your corporate website', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_install_jetpack_boost',
									'title'       => __( 'Install Jetpack Boost or Caching Plugin', 'wp-module-next-steps' ),
									'description' => __( 'Improve website loading speed', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack-boost',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_enable_auto_backups',
									'title'       => __( 'Enable Automatic Backups & Update Alerts', 'wp-module-next-steps' ),
									'description' => __( 'Protect your website with regular backups', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/security',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_install_security_plugin',
									'title'       => __( 'Install a Security Plugin', 'wp-module-next-steps' ),
									'description' => __( 'Add security protection against threats', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_setup_staging_site',
									'title'       => __( 'Set Up a Staging Site', 'wp-module-next-steps' ),
									'description' => __( 'Test updates and redesigns safely', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/tools',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
						array(
							'id'          => 'monitor_improve',
							'label'       => __( 'Monitor & Improve', 'wp-module-next-steps' ),
							'description' => __( 'Track performance and plan improvements', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_review_traffic_engagement',
									'title'       => __( 'Review Traffic & Engagement', 'wp-module-next-steps' ),
									'description' => __( 'Analyze visitor behavior in Google Analytics', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_track_form_submissions',
									'title'       => __( 'Track Form Submissions or Lead Goals', 'wp-module-next-steps' ),
									'description' => __( 'Monitor lead generation effectiveness', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/traffic',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_run_speed_test',
									'title'       => __( 'Run a Speed Test', 'wp-module-next-steps' ),
									'description' => __( 'Test site speed with PageSpeed Insights or GTmetrix', 'wp-module-next-steps' ),
									'href'        => 'https://developers.google.com/speed/pagespeed/insights/',
									'status'      => 'new',
									'priority'    => 3,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_plan_next_content',
									'title'       => __( 'Plan Your Next Content or Campaign Update', 'wp-module-next-steps' ),
									'description' => __( 'Schedule regular content and marketing updates', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/edit.php',
									'status'      => 'new',
									'priority'    => 4,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
			),
		) );
	}

	/**
	 * Update task status
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $task_id Task ID
	 * @param string $status New status
	 * @return bool
	 */
	public static function update_task_status( string $track_id, string $section_id, string $task_id, string $status ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$success = $plan->update_task_status( $track_id, $section_id, $task_id, $status );
		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}

	/**
	 * Get task by IDs
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $task_id Task ID
	 * @return Task|null
	 */
	public static function get_task( string $track_id, string $section_id, string $task_id ): ?Task {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return null;
		}

		return $plan->get_task( $track_id, $section_id, $task_id );
	}

	/**
	 * Add task to a section
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param Task $task Task to add
	 * @return bool
	 */
	public static function add_task( string $track_id, string $section_id, Task $task ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$section = $plan->get_section( $track_id, $section_id );
		if ( ! $section ) {
			return false;
		}

		$success = $section->add_task( $task );
		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}



	/**
	 * Reset plan to defaults
	 *
	 * @return Plan
	 */
	public static function reset_plan(): Plan {
		delete_option( self::OPTION );
		return self::load_default_plan();
	}

	/**
	 * Get plan statistics
	 *
	 * @return array
	 */
	public static function get_plan_stats(): array {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return array();
		}

		return array(
			'completion_percentage'    => $plan->get_completion_percentage(),
			'total_tasks'              => $plan->get_total_tasks_count(),
			'completed_tasks'          => $plan->get_completed_tasks_count(),
			'total_sections'           => $plan->get_total_sections_count(),
			'completed_sections'       => $plan->get_completed_sections_count(),
			'total_tracks'             => $plan->get_total_tracks_count(),
			'completed_tracks'         => $plan->get_completed_tracks_count(),
			'is_completed'             => $plan->is_completed(),
		);
	}

	/**
	 * Update section open state
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param bool $open Open state
	 * @return bool
	 */
	public static function update_section_status( string $track_id, string $section_id, bool $open ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$success = $plan->update_section_open_state( $track_id, $section_id, $open );
		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}
} 