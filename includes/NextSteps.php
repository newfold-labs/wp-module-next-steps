<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\ModuleLoader\Container;
use NewfoldLabs\WP\Module\NextSteps\I18nService;
use NewfoldLabs\WP\Module\Data\HiiveConnection;
use NewfoldLabs\WP\Module\Data\SiteCapabilities;

/**
 * Manages all the functionalities for the module.
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
		// includes until autoloading is set up
		include_once NFD_NEXTSTEPS_DIR . '/includes/StepsApi.php';
		include_once NFD_NEXTSTEPS_DIR . '/includes/NextStepsWidget.php';
		include_once NFD_NEXTSTEPS_DIR . '/includes/I18nService.php';

		$hiive           = new HiiveConnection();
		self::$steps_api = new StepsApi( $hiive );
		$this->container = $container;
		\add_action( 'rest_api_init', array( $this, 'init_steps_apis' ) );
		\add_action( 'admin_enqueue_scripts', array( __CLASS__, 'nextsteps_assets' ) );

		\add_filter( 'nfd_plugin_subnav', array( $this, 'add_nfd_subnav' ) );

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
	 * Enqueue assets and set locals.
	 */
	public static function nextsteps_assets() {
		$asset_file = NFD_NEXTSTEPS_DIR . '/build/next-steps/bundle.asset.php';
		if ( is_readable( $asset_file ) ) {
			$asset = include_once $asset_file;
		} else {
			return;
		}

		\wp_register_script(
			'nextsteps',
			NFD_NEXTSTEPS_PLUGIN_URL . 'vendor/newfold-labs/wp-module-next-steps/build/next-steps/bundle.js',
			array_merge(
				$asset['dependencies'],
				array( 'nfd-installer' ),
			),
			$asset['version'],
			true
		);

		\wp_register_style(
			'nextsteps-style',
			NFD_NEXTSTEPS_PLUGIN_URL . 'vendor/newfold-labs/wp-module-next-steps/build/next-steps/next-steps.css',
			null,
			$asset['version']
		);

		// Only enqueue on next-steps page
		// $screen = \get_current_screen();
		// if ( isset( $screen->id ) && false !== strpos( $screen->id, 'next-steps' ) ) {
			\wp_enqueue_script( 'nextsteps' );
			\wp_enqueue_style( 'nextsteps-style' );

			$next_steps_data = json_decode( \wp_json_encode( self::$steps_api->get_steps()->data ), true );

			\wp_localize_script(
				'nextsteps',
				'NewfoldNextSteps',
				array_merge(
					$next_steps_data,
				)
			);
		// }
	}

}
