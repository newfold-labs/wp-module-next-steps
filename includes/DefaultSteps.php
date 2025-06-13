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
class DefaultSteps {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Register the widget
		\add_action( 'init', array( __CLASS__, 'load_default_steps' ), 1 );
        \add_action( 'activated_plugin', array( __CLASS__, 'add_store_steps_on_woocommerce_activation' ), 10, 2 );
	}

   /**
    * Default site steps.
    */
    public static function load_default_steps() {
        // $next_steps = false; // useful for resetting while debugging
        // if no steps found
        if ( ! get_option( StepsApi::OPTION ) ) {
            // add default steps
            StepsApi::set_data( self::get_defaults() );
        }
    }

    /**
     * If WooCommerce is activated, add store steps to next steps.
     *
     * @param string $plugin The plugin being activated.
     * @param bool $network_wide Whether the plugin is being activated network-wide.
     */
    public static function add_store_steps_on_woocommerce_activation( $plugin, $network_wide ) {
        // Only run if WooCommerce is being activated
        if ( $plugin !== 'woocommerce/woocommerce.php' ) {
            return;
        }

        // Add or update steps using StepsApi
        StepsApi::add_steps( self::get_default_store_data() );
    }

   /**
    * Get default steps based on site criteria.
    *
    * @return Array array of default step data
    */
    public static function get_defaults() {
        $defaults = self::get_default_site_data();

        if ( self::is_blog() ) {
            // add default blog steps
            $defaults = array_merge( 
                $defaults,
                self::get_default_blog_data()
            );
        }

        if ( self::is_store() ) {
            // add default store steps
            $defaults = array_merge( 
                $defaults,
                self::get_default_store_data()
            );
        }
        return $defaults;
    }

    /**
     * Determine if site is blog
     * 
     * @return Boolean
     */
    public static function is_blog() {
        if ( post_type_exists( 'post' ) ) {
            return true;
        }
        return false;
    }

    /**
     * Determine if site is story
     * 
     * @return Boolean
     */
    public static function is_store() {
        // if ( post_type_exists( 'product' ) ) {
        //     return true;
        // }
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            return true;
        }
        // check solutions data too?

        return false;
    }
    
    /**
     * Default site steps data.
     * 
     * These apply to all sites
     */
    public static function get_default_site_data() {
        return array(
            array(
                'id'          => 'explore_addons',
                'title'       => __( 'Explore the premium tools included in your solution', 'wp-module-next-steps' ),
                'description' => __( 'A bundle of features designed to elevate your online experience', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/admin.php?page=solutions&category=all',
                'status'      => 'new',
                'priority'    => 10,
                'source'      => 'wp-module-next-steps',
            ),
            array(
                'id'          => 'add_new_page',
                'title'       => __( 'Add a new page', 'wp-module-next-steps' ),
                'description' => __( 'Create a new page on your site.', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
                'status'      => 'new',
                'priority'    => 20,
                'source'      => 'wp-module-next-steps',
                // 'autocomplete' => function () {
                //     $query = new \WP_Query([
                //         'post_type'      => 'page',
                //         'post_status'    => 'publish',
                //         'posts_per_page' => 1,
                //         'fields'         => 'ids', // Faster, only fetch IDs
                //     ]);
                //     return $query->have_posts();
                // },
            ),
            array(
                'id'          => 'upload_media',
                'title'       => __( 'Add Media', 'wp-module-next-steps' ),
                'description' => __( 'Upload a new image to your site.', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/media-new.php',
                'status'      => 'new',
                'priority'    => 21,
                'source'      => 'wp-module-next-steps',
                // 'autocomplete' => function () {
                //     $query = new \WP_Query([
                //         'post_type'      => 'attachment',
                //         'post_mime_type' => 'image',
                //         'post_status'    => 'inherit',
                //         'posts_per_page' => 1,
                //         'fields'         => 'ids', // Faster, only fetch IDs
                //     ]);
                //     return $query->have_posts();
                // },
            ),
            array(
                'id'          => 'yoast_academy',
                'title'       => __( 'Sign up for Yoast SEO Academy', 'wp-module-next-steps' ),
                'description' => __( 'Master WordPress SEO with expert training.', 'wp-module-next-steps' ),
                'href'        => 'https://yoast.com/academy/',
                'status'      => 'new',
                'priority'    => 40,
                'source'      => 'wp-module-next-steps',
            ),
            array(
                'id'          => 'jetpack_social',
                'title'       => __( 'Enable Jetpack to connect to your social media accounts', 'wp-module-next-steps' ),
                'description' => __( 'Enable Jetpack to connect to your social media accounts', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/admin.php?page=jetpack#/sharing',
                'status'      => 'new',
                'priority'    => 60,
                'source'      => 'wp-module-next-steps',
            ),
        );
    }

    /**
     * Default blog steps data.
     */
    public static function get_default_blog_data() {
        return array(
            array(
                'id'          => 'add_new_post',
                'title'       => __( 'Add your first blog post', 'wp-module-next-steps' ),
                'description' => __( 'Create your first blog post and start sharing your thoughts', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=post',
                'status'      => 'new',
                'priority'    => 21,
                'source'      => 'wp-module-next-steps',
            ),
            array(
                'id'          => 'configure_blog_settings',
                'title'       => __( 'Configure your blog settings', 'wp-module-next-steps' ),
                'description' => __( 'Set up your blog settings to enhance your blogging experience', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/options-general.php',
                'status'      => 'new',
                'priority'    => 30,
                'source'      => 'wp-module-next-steps',
            ),
        );
    }

    /**
     * Default ecommerce steps data.
     */
    public static function get_default_store_data() {
        return array(
            array(
                'id'          => 'store_info',
                'title'       => __( 'Add your store info', 'wp-module-next-steps' ),
                'description' => __( 'Build trust and present yourself in the best way to your customers', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/details?highlight=details',
                'status'      => 'new',
                'priority'    => 31,
                'source'      => 'wp-module-next-steps',
            ),
            array(
                'id'          => 'add_product',
                'title'       => __( 'Add your first product', 'wp-module-next-steps' ),
                'description' => __( 'Create or import a product and bring your store to life', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=product',
                'status'      => 'new',
                'priority'    => 32,
                'source'      => 'wp-module-next-steps',
            ),
            array(
                'id'          => 'connect_payment_processor',
                'title'       => __( 'Connect a payment processor', 'wp-module-next-steps' ),
                'description' => __( 'Get ready to receive your first payments via PayPal or credit card', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/payments',
                'status'      => 'new',
                'priority'    => 41,
                'source'      => 'wp-module-next-steps',
            ),
            array(
                'id'          => 'configure_tax',
                'title'       => __( 'Configure tax settings', 'wp-module-next-steps' ),
                'description' => __( 'Set up your tax options to start selling', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/details?highlight=tax',
                'status'      => 'new',
                'priority'    => 42,
                'source'      => 'wp-module-next-steps',
            ),
            array(
                'id'          => 'setup_shipping',
                'title'       => __( 'Setup shipping options', 'wp-module-next-steps' ),
                'description' => __( 'Setup shipping options', 'wp-module-next-steps' ),
                'href'        => '{siteUrl}/wp-admin/admin.php?page=bluehost#/store/details?highlight=shipping',
                'status'      => 'new',
                'priority'    => 43,
                'source'      => 'wp-module-next-steps',
            ),
        );
    }

}