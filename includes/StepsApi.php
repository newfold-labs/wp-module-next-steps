<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\Data\HiiveConnection;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class StepsApi
 */
class StepsApi {

	/**
	 * Transient name where data is stored.
	 */
	const OPTION = 'nfd_next_steps';

	/**
	 * Hiive API endpoint for fetching site entitlements.
	 */
	const HIIVE_API_ENDPOINT = '/sites/v1/nextsteps';

	/**
	 * Instance of the HiiveConnection class.
	 *
	 * @var HiiveConnection
	 */
	private $hiive;

	/**
	 * REST namespace
	 *
	 * @var string
	 */
	private $namespace;

	/**
	 * REST base
	 *
	 * @var string
	 */
	private $rest_base;

	/**
	 * EntitilementsApi constructor.
	 *
	 * @param HiiveConnection $hiive           Instance of the HiiveConnection class.
	 */
	public function __construct( HiiveConnection $hiive ) {
		$this->hiive     = $hiive;
		$this->namespace = 'newfold-next-steps/v1';
		$this->rest_base = '/steps';
	}

	/**
	 * Register Entitlement routes.
	 */
	public function register_routes() {

		// Add route for fetching steps
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_steps' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Add route for updating a step status
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/status',
			array(
				'methods'             => \WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_step_status' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'id'     => array(
						'required'          => true,
						'validate_callback' => function ( $value ) {
							return is_string( $value );
						},
					),
					'status' => array(
						'required'          => true,
						'validate_callback' => function ( $value ) {
							return is_string( $value );
						},
					),
				),
			)
		);

	}

	/**
	 * Set the transient where entitlements are stored (6 Hours).
	 *
	 * @param array     $data           Data to be stored
	 */
	public function set_data( $data ) {
		update_option( self::OPTION, $data );
	}

	/**
	 * Get entitlements of a site.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_steps() {
		$next_steps = get_option( self::OPTION );
		// $next_steps = false;

		if ( false === $next_steps ) {

			// TODO: update response to be available without connection and return solutions categories and premium
			// If there is no Hiive connection, bail.
			if ( ! HiiveConnection::is_connected() ) {
				// If no connection, give an empty response.
				return new WP_REST_Response(
					array(
						array(
							'id' => 'add_new_page',
							'title' => __( 'Add a new page', 'wp-module-next-steps' ),
							'description' => __( 'Create a new page on your site.', 'wp-module-next-steps' ),
							'status' => 'new',
							'href' => '{siteUrl}/wp-admin/post-new.php?post_type=page',
							'priority' => 2,
						),
						array(
							'id' => 'upload_media',
							'title' => __( 'Add Media', 'wp-module-next-steps' ),
							'description' => __( 'Upload a new image to your site.', 'wp-module-next-steps' ),
							'href' => '{siteUrl}/wp-admin/media-new.php',
							'status' => 'done',
							'priority' => 2,
						),
						array(
							'id' => 'yoast_academy',
							'title' => __( 'Sign up for Yoast SEO Academy', 'wp-module-next-steps' ),
							'description' => __( 'Master WordPress SEO with expert training.', 'wp-module-next-steps' ),
							'href' => 'https://yoast.com/academy/',
							'status' => 'new',
							'priority' => 4,
						),
						array(
							'id' => 'explore_addons',
							'title' => __( 'Explore the premium tools included in your solution', 'wp-module-next-steps' ),
							'description' => __( 'A bundle of features designed to elevate your online experience', 'wp-module-next-steps' ),
							'href' => '{siteUrl}/wp-admin/admin.php?page=solutions&category=all',
							'status' => 'dismissed',
							'priority' => 1,
						),
						array(
							'id' => 'add_product',
							'title' => __( 'Add your first product', 'wp-module-next-steps' ),
							'description' => __( 'Create or import a product and bring your store to life', 'wp-module-next-steps' ),
							'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
							'status' => 'new',
							'priority' => 5,
						),
						array(
							'id' => 'store_info',
							'title' => __( 'Add your store info', 'wp-module-next-steps' ),
							'description' => __( 'Build trust and present yourself in the best way to your customers', 'wp-module-next-steps' ),
							'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
							'status' => 'new',
							'priority' => 3,
						),
						array(
							'id' => 'connect_payment_processor',
							'title' => __( 'Connect a payment processor', 'wp-module-next-steps' ),
							'description' => __( 'Get ready to receive your first payments via PayPal or credit card', 'wp-module-next-steps' ),
							'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
							'status' => 'new',
							'priority' => 4,
						),
						array(
							'id' => 'configure_tax',
							'title' => __( 'Configure tax settings', 'wp-module-next-steps' ),
							'description' => __( 'Set up your tax options to start selling', 'wp-module-next-steps' ),
							'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
							'status' => 'new',
							'priority' => 4,
						),
						array(
							'id' => 'jetpack_social',
							'title' => __( 'Enable Jetpack to connect to your social media accounts', 'wp-module-next-steps' ),
							'description' => __( '', 'wp-module-next-steps' ),
							'href' => '{siteUrl}/wp-admin/post-new.php?post_type=product',
							'status' => 'new',
							'priority' => 6,
						),
					),
					200
				);
			}

			// Get fresh entitlements data from Hiive API
			$response = wp_remote_get(
				NFD_HIIVE_URL . self::HIIVE_API_ENDPOINT,
				array(
					'headers' => array(
						'Content-Type'  => 'application/json',
						'Accept'        => 'application/json',
						'Authorization' => 'Bearer ' . HiiveConnection::get_auth_token(),
					),
				)
			);

			if ( is_wp_error( $response ) ) {
				return new WP_REST_Response( 
					array( 'message' => 'An error occurred with the next steps response.' ),
					500
				);
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			if ( $data && is_array( $data ) ) {
				$this->set_data( $data );
			}
		}

		return new WP_REST_Response( $next_steps, 200 );
	}
	/**
	 * Update a step status.
	 *
	 * @param string $id The ID of the step to update.
	 * @param string $status The new status of the step.
	 * @return WP_REST_Response|WP_Error The response object on success, or WP_Error on failure.
	 */
	public function update_step_status(  \WP_REST_Request $request ) {
		$id     = $request->get_param( 'id' );
		$status = $request->get_param( 'status' );
		// validate parameters
		if ( empty( $id ) || empty( $status ) ) {
			return new WP_Error( 'invalid_params', __( 'Invalid parameters provided.', 'wp-module-next-steps' ), array( 'status' => 400 ) );
		}
		if ( ! in_array( $status, array( 'new', 'done', 'dismissed' ), true ) ) {
			return new WP_Error( 'invalid_status', __( 'Invalid status provided.', 'wp-module-next-steps' ), array( 'status' => 400 ) );
		}
		// Get the current steps from the option
		$steps = get_option( self::OPTION, array() );

		if ( ! is_array( $steps ) || empty( $steps ) ) {
			return new WP_Error( 'no_steps', __( 'No steps found.', 'wp-module-next-steps' ), array( 'status' => 404 ) );
		}
		// Find the step with the given ID and update its status
		$step_found = false;
		foreach ( $steps as &$step ) {
			if ( $step['id'] === $id ) {
				$step['status'] = $status;
				$step_found = true;
				break;
			}
		}
		if ( ! $step_found ) {
			return new WP_Error( 'step_not_found', __( 'Step not found.', 'wp-module-next-steps' ), array( 'status' => 404 ) );
		}
		// Update the option with the modified steps
		update_option( self::OPTION, $steps );

		return new WP_REST_Response( $steps, 200 );
	}

}
