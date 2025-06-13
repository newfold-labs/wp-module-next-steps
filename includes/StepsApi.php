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

		// Add route for adding steps
		register_rest_route(
			$this->namespace,
			$this->rest_base . '/add',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'add_steps' ),
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
	 * @param array $steps           Data to be stored
	 */
	public static function set_data( $steps ) {
		update_option( self::OPTION, $steps );
	}

	/**
	 * Get entitlements of a site.
	 *
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public static function get_steps() {
		$next_steps = get_option( self::OPTION );

		// set default steps if none are found
		if ( false === $next_steps ) {
			// get default steps
			$next_steps = DefaultSteps::get_defaults();
			self::set_data( $next_steps );
		}

		// TODO
		// check each steps callback to determine if completed - smart next steps autocomplete
		// each step can define a callback that will be called to determine if the step is completed
		// for example add post can check if a post exists in the site or add media can check if media has been uploaded

		return new WP_REST_Response( $next_steps, 200 );
	}

	/**
	 * Add steps to the current steps list.
	 *
	 * For each new step:
	 * - If a step with the same 'id' exists, update its 'title', 'description', 'href', and 'priority' fields
	 *   (but NOT 'status'), and only if the new value is different from the existing one.
	 * - If a step with the same 'id' does not exist, add it to the list, defaulting 'status' to 'new' if not set.
	 *
	 * @param array $new_steps Array of new steps to add or update.
	 * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
	 */
	public static function add_steps( $new_steps ) {
		// Fetch the current steps from the database option.
		$steps = get_option( self::OPTION );
		if ( ! isset( $steps ) || ! is_array( $steps ) ) {
			$steps = array();
		}

		// List of fields to sync from new steps to existing steps.
		$sync_fields = array( 'title', 'description', 'href', 'priority' );

		// Iterate through each new step to add or update.
		foreach ( $new_steps as $new_step ) {
			// Skip steps without an 'id'.
			if ( ! isset( $new_step['id'] ) ) {
				continue;
			}

			// Look for any existing steps with the same id.
			$existing_index = null;
			foreach ( $steps as $idx => $existing_step ) {
				if ( isset( $existing_step['id'] ) && $existing_step['id'] === $new_step['id'] ) {
					$existing_index = $idx;
					break;
				}
			}

			// If existing step found
			if ( $existing_index !== null ) {
				// Update allowed fields if the value is different.
				foreach ( $sync_fields as $field ) {
					if (
						isset( $new_step[ $field ] ) &&
						( ! isset( $steps[ $existing_index ][ $field ] ) ||
						$steps[ $existing_index ][ $field ] !== $new_step[ $field ] )
					) {
						$steps[ $existing_index ][ $field ] = $new_step[ $field ];
					}
				}
			} else {
				// Add new step, defaulting status to 'new' if not set.
				if ( ! isset( $new_step['status'] ) ) {
					$new_step['status'] = 'new';
				}
				$steps[] = $new_step;
			}
		}

		// Save the updated steps back to the database.
		self::set_data( $steps );
		return new \WP_REST_Response( $steps, 200 );
	}

	/**
	 * Update a step status.
	 *
	 * @param \WP_REST_Request $request  The REST request object.
	 * @return WP_REST_Response|WP_Error The response object on success, or WP_Error on failure.
	 */
	public static function update_step_status( \WP_REST_Request $request ) {
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
				$step_found     = true;
				break;
			}
		}
		if ( ! $step_found ) {
			return new WP_Error( 'step_not_found', __( 'Step not found.', 'wp-module-next-steps' ), array( 'status' => 404 ) );
		}
		// Update the option with the modified steps
		self::set_data( $steps );

		return new WP_REST_Response( $steps, 200 );
	}
}
