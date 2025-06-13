<?php

namespace NewfoldLabs\WP\Module\NextSteps;

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
	 */
	public function __construct( ) {
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
		// newfold-next-steps/step/update
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
	 * Set the option where steps are stored.
	 *
	 * @param array $data           Data to be stored
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
		// $next_steps = false; // useful for resetting while debugging
		// set default steps if none are found
		if ( false === $next_steps ) {
			// get default steps
			$next_steps = array(
				'steps' => DefaultSteps::get_defaults()
			);
			$this->set_data( $next_steps );
		}

		// TODO
		// check each steps callback to determine if completed - smart next steps autocomplete
		// each step can define a callback that will be called to determine if the step is completed
		// for example add post can check if a post exists in the site or add media can check if media has been uploaded

		return new WP_REST_Response( $next_steps, 200 );
	}
	/**
	 * Update a step status.
	 *
	 * @param \WP_REST_Request $request  The REST request object.
	 * @return WP_REST_Response|WP_Error The response object on success, or WP_Error on failure.
	 */
	public function update_step_status( \WP_REST_Request $request ) {
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
		foreach ( $steps['steps'] as &$step ) {
			if ( $step['id'] === $id ) {
				$step['status'] = $status;
				$step_found     = true;
				break;
			}
		}
		if ( ! $step_found ) {
			return new WP_Error( 'step_not_found', __( 'Step not found.', 'wp-module-next-steps' ), array( 'status' => 404 ) );
		}
		// Update the option with the modified steps
		$this->set_data( $steps );

		return new WP_REST_Response( $steps, 200 );
	}
}
