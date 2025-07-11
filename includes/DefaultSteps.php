<?php
/**
 * Default Next Steps Data.
 *
 * @package WPPluginBluehost
 */

namespace NewfoldLabs\WP\Module\NextSteps;

use function NewfoldLabs\WP\ModuleLoader\container;
use function NewfoldLabs\WP\Context\getContext;

/**
 * NewfoldLabs\WP\Module\NextSteps\NextStepsWidget
 *
 * Adds a Next Steps dashboard widget to the WordPress dashboard.
 */
class DefaultSteps
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Register the widget
		\add_action('init', array(__CLASS__, 'load_default_steps'), 1);
		// \add_action(
		// 	'activated_plugin',
		// 	array( __CLASS__, 'add_store_steps_on_woocommerce_activation' ),
		// 	10,
		// 	2
		// );
	}

	/**
	 * Default site steps.
	 */
	public static function load_default_steps()
	{
		// $next_steps = false; // for resetting data while debugging
		// if no steps found
		if (!get_option(StepsApi::OPTION)) {
			// add default steps
			StepsApi::set_data(self::get_defaults());
		}
	}

	/**
	 * If WooCommerce is activated, add store steps to next steps.
	 *
	 * @param string $plugin The plugin being activated.
	 * @param bool   $network_wide Whether the plugin is being activated network-wide.
	 */
	public static function add_store_steps_on_woocommerce_activation($plugin, $network_wide)
	{
		// Only for WooCommerce
		if ('woocommerce/woocommerce.php' === $plugin) {
			// Add or update steps using StepsApi
			StepsApi::add_steps(self::get_default_store_data());
		}

		return;
	}

	/**
	 * Get default steps based on site criteria.
	 *
	 * @return Array array of default step data
	 */
	public static function get_defaults()
	{
		// $defaults = self::get_default_site_data();

		// if ( self::is_blog() ) {
		// 	// add default blog steps
		// 	$defaults = array_merge(
		// 		$defaults,
		// 		self::get_default_blog_data()
		// 	);
		// }

		// if ( self::is_store() ) {
		// 	// add default store steps
		// 	$defaults = array_merge(
		// 		$defaults,
		// 		self::get_default_store_data()
		// 	);
		// }
		// return $defaults;

		return self::get_store_setup_data();
	}

	/**
	 * Determine if site is blog
	 *
	 * @return Boolean
	 */
	public static function is_blog()
	{
		if (post_type_exists('post')) {
			return true;
		}
		return false;
	}

	/**
	 * Determine if the site is a store
	 *
	 * @return Boolean
	 */
	public static function is_store()
	{
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
			return true;
		}
		// check solutions data too?
		// check for products post type?

		return false;
	}

	/**
	 * Default site steps data.
	 *
	 * These apply to all sites
	 */
	public static function get_default_site_data()
	{
		return array(
			array(
				'id' => 'explore_addons',
				'title' => __('Explore the premium tools included in your solution', 'wp-module-next-steps'),
				'description' => __('A bundle of features designed to elevate your online experience', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/admin.php?page=solutions&category=all',
				'status' => 'new',
				'priority' => 10,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'add_new_page',
				'title' => __('Add a new page', 'wp-module-next-steps'),
				'description' => __('Create a new page on your site.', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/post-new.php?post_type=page',
				'status' => 'new',
				'priority' => 20,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'upload_media',
				'title' => __('Add Media', 'wp-module-next-steps'),
				'description' => __('Upload a new image to your site.', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/media-new.php',
				'status' => 'new',
				'priority' => 21,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'yoast_academy',
				'title' => __('Sign up for Yoast SEO Academy', 'wp-module-next-steps'),
				'description' => __('Master WordPress SEO with expert training.', 'wp-module-next-steps'),
				'href' => 'https://yoast.com/academy/',
				'status' => 'new',
				'priority' => 40,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'jetpack_social',
				'title' => __('Enable Jetpack to connect to your social media accounts', 'wp-module-next-steps'),
				'description' => __('Enable Jetpack to connect to your social media accounts', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
				'status' => 'new',
				'priority' => 60,
				'source' => 'wp-module-next-steps',
			),
		);
	}

	/**
	 * Default blog steps data.
	 */
	public static function get_default_blog_data()
	{
		return array(
			array(
				'id' => 'add_new_post',
				'title' => __('Add your first blog post', 'wp-module-next-steps'),
				'description' => __('Create your first blog post and start sharing your thoughts', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/post-new.php?post_type=post',
				'status' => 'new',
				'priority' => 21,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'configure_blog_settings',
				'title' => __('Configure your blog settings', 'wp-module-next-steps'),
				'description' => __('Set up your blog settings to enhance your blogging experience', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/options-general.php',
				'status' => 'new',
				'priority' => 30,
				'source' => 'wp-module-next-steps',
			),
		);
	}

	/**
	 * Default ecommerce steps data.
	 */
	public static function get_default_store_data()
	{
		return array(
			array(
				'id' => 'store_info',
				'title' => __('Add your store info', 'wp-module-next-steps'),
				'description' => __('Build trust and present yourself in the best way to your customers', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/details?highlight=details',
				'status' => 'new',
				'priority' => 31,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'add_product',
				'title' => __('Add your first product', 'wp-module-next-steps'),
				'description' => __('Create or import a product and bring your store to life', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
				'status' => 'new',
				'priority' => 32,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'connect_payment_processor',
				'title' => __('Connect a payment processor', 'wp-module-next-steps'),
				'description' => __('Get ready to receive your first payments via PayPal or credit card', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/payments',
				'status' => 'new',
				'priority' => 41,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'configure_tax',
				'title' => __('Configure tax settings', 'wp-module-next-steps'),
				'description' => __('Set up your tax options to start selling', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/details?highlight=tax',
				'status' => 'new',
				'priority' => 42,
				'source' => 'wp-module-next-steps',
			),
			array(
				'id' => 'setup_shipping',
				'title' => __('Setup shipping options', 'wp-module-next-steps'),
				'description' => __('Setup shipping options', 'wp-module-next-steps'),
				'href' => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/details?highlight=shipping',
				'status' => 'new',
				'priority' => 43,
				'source' => 'wp-module-next-steps',
			),
		);
	}

	/**
	 * Store setup checklist.
	 * 
	 * @return array
	 */
	public static function get_store_setup_data(): array
	{
		return array(
			'plan' => array(
				'id' => 'store_setup',
				'label' => __('Store Setup', 'wp-module-next-steps'),
				'description' => __('To get the best experience, we recommend completing your Store Setup:', 'wp-module-next-steps'),
				'tracks' => array(
					array(
						'id' => 'store_build_track',
						'label' => __('Step 1: Build', 'wp-module-next-steps'),
						'sections' => array(
							array(
								'id' => 'basic_store_setup',
								'label' => __('Basic Store Steup', 'wp-module-next-steps'),
								'description' => __('Complete the basic setup of your store', 'wp-module-next-steps'),
								'tasks' => array(
									array(
										'id' => 'store_build_basic_quick_setup',
										'title' => __('Quick Setup', 'wp-module-next-steps'),
										'description' => __('Build trust and present yourself in the best way to your customers', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/details?highlight=details',
										'status' => 'new',
										'priority' => 1,
										'source' => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id' => 'customize_store',
								'label' => __('Customize your store', 'wp-module-next-steps'),
								'description' => __('Customize your store to match your brand', 'wp-module-next-steps'),
								'tasks' => array(
									array(
										'id' => 'store_build_customize_logo',
										'title' => __('Upload your logo', 'wp-module-next-steps'),
										'description' => __('Customize your store to match your brand', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'done',
										'priority' => 1,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_customize_colors',
										'title' => __('Choose colors and fonts', 'wp-module-next-steps'),
										'description' => __('Customize your store to match your brand', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'dismissed',
										'priority' => 2,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_customize_header',
										'title' => __('Customize header', 'wp-module-next-steps'),
										'description' => __('Customize your store to match your brand', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 3,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_customize_footer',
										'title' => __('Customize footer', 'wp-module-next-steps'),
										'description' => __('Customize your store to match your brand', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 4,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_customize_home',
										'title' => __('Customize home page', 'wp-module-next-steps'),
										'description' => __('Customize your store to match your brand', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 5,
										'source' => 'wp-module-next-steps',
									)
								),
							),
							array(
								'id' => 'products',
								'label' => __('Set Up Products', 'wp-module-next-steps'),
								'description' => __('Add products to your store', 'wp-module-next-steps'),
								'tasks' => array(
									array(
										'id' => 'store_build_product_add',
										'title' => __('Add a product', 'wp-module-next-steps'),
										'description' => __('Create or import a product and bring your store to life', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
										'status' => 'new',
										'priority' => 1,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_product_page',
										'title' => __('Customize the product page', 'wp-module-next-steps'),
										'description' => __('Customize the product page', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 2,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_product_shop',
										'title' => __('Customize the shop page', 'wp-module-next-steps'),
										'description' => __('Customize the shop page to showcase your products', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 3,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_product_cart',
										'title' => __('Customize the cart page', 'wp-module-next-steps'),
										'description' => __('Customize the cart page to showcase your products', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 4,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_product_checkout',
										'title' => __('Customize the checkout flow', 'wp-module-next-steps'),
										'description' => __('Customize the checkout flow to showcase your products', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 5,
										'source' => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id' => 'payment_shipping',
								'label' => __('Set Up Payments and Shipping', 'wp-module-next-steps'),
								'description' => __('Add products to your store', 'wp-module-next-steps'),
								'tasks' => array(
									array(
										'id' => 'store_build_payment_setup',
										'title' => __('Set Up Payments', 'wp-module-next-steps'),
										'description' => __('Get ready to receive your first payments via PayPal or credit card', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
										'status' => 'new',
										'priority' => 1,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_shipping_setup',
										'title' => __('Set Up Shipping', 'wp-module-next-steps'),
										'description' => __('Set up shipping options', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 2,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_tax_setup',
										'title' => __('Set Up Taxes', 'wp-module-next-steps'),
										'description' => __('Set up tax options to start selling', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/customize.php',
										'status' => 'new',
										'priority' => 3,
										'source' => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id' => 'legal',
								'label' => __('Set up Legal Pages', 'wp-module-next-steps'),
								'description' => __('Set up legal pages to comply with regulations', 'wp-module-next-steps'),
								'tasks' => array(
									array(
										'id' => 'store_build_legal_privacy',
										'title' => __('Set Up Privacy Policy', 'wp-module-next-steps'),
										'description' => __('Set up a privacy policy page to comply with regulations', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 1,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_legal_terms',
										'title' => __('Set Up Terms and Conditions', 'wp-module-next-steps'),
										'description' => __('Set up a terms and conditions page to comply with regulations', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 2,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_build_legal_returns',
										'title' => __('Set Up Returns Policy', 'wp-module-next-steps'),
										'description' => __('Set up a returns policy page to comply with regulations', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 3,
										'source' => 'wp-module-next-steps',
									),
								),
							),
						),
					),
					array(
						'id' => 'store_brand_track',
						'label' => __('Step 2: Brand', 'wp-module-next-steps'),
						'sections' => array(
							array(
								'id' => 'first_marketing_steps',
								'label' => __('First Marketing Steps', 'wp-module-next-steps'),
								'description' => __('Get your store ready for marketing', 'wp-module-next-steps'),
								'tasks' => array(
									array(
										'id' => 'store_brand_marketing_social',
										'title' => __('Enable Social Login Register for your customers', 'wp-module-next-steps'),
										'description' => __('Enable Social Login Register for your customers', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 1,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_brand_marketing_discount',
										'title' => __('Configure Welcome Discount Popup', 'wp-module-next-steps'),
										'description' => __('Configure Welcome Discount Popup', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 2,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_brand_marketing_giftcard',
										'title' => __('Create a gift card to sell in your shop', 'wp-module-next-steps'),
										'description' => __('Create a gift card to sell in your shop', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 3,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_brand_marketing_abandoned',
										'title' => __('Enable Abandoned Cart Emails', 'wp-module-next-steps'),
										'description' => __('Enable Abandoned Cart Emails', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 4,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_brand_marketing_emails',
										'title' => __('Customize your store emails', 'wp-module-next-steps'),
										'description' => __('Customize your store emails', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 5,
										'source' => 'wp-module-next-steps',
									),
									array(
										'id' => 'store_brand_marketing_analytics',
										'title' => __('Add Google Analytics ', 'wp-module-next-steps'),
										'description' => __('Add Google Analytics ', 'wp-module-next-steps'),
										'href' => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status' => 'new',
										'priority' => 6,
										'source' => 'wp-module-next-steps',
									)
								),
							),
						),
					),
				),
			),
		);
	}
}
