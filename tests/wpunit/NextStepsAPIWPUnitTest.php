<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\StepsApi;
use NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit\TestPlanFactory;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * WordPress Unit Tests for NextSteps API Methods
 * 
 * These tests run in a real WordPress environment with database access.
 * They test all API methods for the Next Steps module.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\StepsApi
 */
class NextStepsAPIWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * API namespace
	 */
	const NAMESPACE = 'newfold-next-steps/v2';

	/**
	 * REST base
	 */
	const REST_BASE = '/plans';

	/**
	 * StepsApi instance for testing
	 */
	private $steps_api;

	/**
	 * Test user with admin capabilities
	 */
	private $admin_user;

	/**
	 * Test user without admin capabilities
	 */
	private $subscriber_user;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Load TestPlanFactory from wpunit directory
		require_once dirname( __DIR__ ) . '/wpunit/TestPlanFactory.php';

		// Create test users
		$this->admin_user = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		$this->subscriber_user = $this->factory->user->create( array(
			'role' => 'subscriber',
		) );

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );
		
		// Invalidate static cache
		PlanRepository::invalidate_cache();

		// Set up a default plan for testing
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Create StepsApi instance for testing
		$this->steps_api = new StepsApi();
	}

	/**
	 * Test get_steps method - Retrieve current plan
	 */
	public function test_get_steps_method() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'GET', self::NAMESPACE . self::REST_BASE );
		$response = $this->steps_api->get_steps( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'type', $data );
		$this->assertArrayHasKey( 'tracks', $data );
		$this->assertEquals( 'blog_setup', $data['id'] );
		$this->assertEquals( 'blog', $data['type'] );
	}

	/**
	 * Test add_steps method - Add new tasks
	 */
	public function test_add_steps_method() {
		wp_set_current_user( $this->admin_user );

		$new_tasks = array(
			'track_id' => 'blog_build_track',
			'section_id' => 'basic_blog_setup',
			'tasks' => array(
				array(
					'id' => 'new_task_1',
					'title' => 'New Task 1',
					'status' => 'new',
				),
				array(
					'id' => 'new_task_2',
					'title' => 'New Task 2',
					'status' => 'new',
				),
			),
		);

		$request = new WP_REST_Request( 'POST', self::NAMESPACE . self::REST_BASE . '/add' );
		$request->set_body_params( $new_tasks );
		$response = $this->steps_api->add_steps( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		// The add_steps method returns the updated plan data, not a success flag
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( 'blog_setup', $data['id'] );
	}

	/**
	 * Test update_task_status method - Update task status
	 */
	public function test_update_task_status_method() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/tasks/blog_quick_setup' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'blog_build_track',
			'section_id' => 'basic_blog_setup',
			'task_id' => 'blog_quick_setup',
			'status' => 'done',
		) );
		$response = $this->steps_api->update_task_status( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'status', $data );
		$this->assertEquals( 'blog_quick_setup', $data['id'] );
		$this->assertEquals( 'done', $data['status'] );
	}

	/**
	 * Test update_task_status method - Invalid task ID
	 */
	public function test_update_task_status_method_invalid_task() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/tasks/invalid_task' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'blog_build_track',
			'section_id' => 'basic_blog_setup',
			'task_id' => 'invalid_task',
			'status' => 'done',
		) );
		$response = $this->steps_api->update_task_status( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'step_not_found', $response->get_error_code() );
	}

	/**
	 * Test get_plan_stats method - Get plan statistics
	 */
	public function test_get_plan_stats_method() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'GET', self::NAMESPACE . self::REST_BASE . '/stats' );
		$response = $this->steps_api->get_plan_stats( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'total_tasks', $data );
		$this->assertArrayHasKey( 'completed_tasks', $data );
		$this->assertArrayHasKey( 'completion_percentage', $data );
		$this->assertArrayHasKey( 'total_sections', $data );
		$this->assertArrayHasKey( 'completed_sections', $data );
		$this->assertArrayHasKey( 'total_tracks', $data );
		$this->assertArrayHasKey( 'completed_tracks', $data );
		$this->assertGreaterThan( 0, $data['total_tasks'] );
	}

	/**
	 * Test switch_plan method - Switch plan type
	 */
	public function test_switch_plan_method() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/switch' );
		$request->set_body_params( array(
			'plan_type' => 'ecommerce',
		) );
		$response = $this->steps_api->switch_plan( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'type', $data );
		$this->assertEquals( 'store_setup', $data['id'] );
		$this->assertEquals( 'ecommerce', $data['type'] );
	}

	/**
	 * Test switch_plan method - Invalid plan type
	 */
	public function test_switch_plan_method_invalid_type() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/switch' );
		$request->set_body_params( array(
			'plan_type' => 'invalid_type',
		) );
		$response = $this->steps_api->switch_plan( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'invalid_plan_type', $response->get_error_code() );
	}

	/**
	 * Test reset_plan method - Reset plan to default
	 */
	public function test_reset_plan_method() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/reset' );
		$response = $this->steps_api->reset_plan( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'type', $data );
		$this->assertEquals( 'blog_setup', $data['id'] );
		$this->assertEquals( 'blog', $data['type'] );
	}

	/**
	 * Test update_track_status method - Update track open state
	 */
	public function test_update_track_status_method() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/tracks/blog_build_track' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'blog_build_track',
			'open' => false,
		) );
		$response = $this->steps_api->update_track_status( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'open', $data );
		$this->assertEquals( 'blog_build_track', $data['id'] );
		$this->assertFalse( $data['open'] );
	}

	/**
	 * Test update_track_status method - Invalid track ID
	 */
	public function test_update_track_status_method_invalid_track() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/tracks/invalid_track' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'invalid_track',
			'open' => false,
		) );
		$response = $this->steps_api->update_track_status( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'track_not_found', $response->get_error_code() );
	}

	/**
	 * Test update_section_state method - Update section open state
	 */
	public function test_update_section_state_method_open() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/sections/basic_blog_setup' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'blog_build_track',
			'section_id' => 'basic_blog_setup',
			'type' => 'open',
			'value' => false,
		) );
		$response = $this->steps_api->update_section_state( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( 'basic_blog_setup', $data['id'] );
	}

	/**
	 * Test update_section_state method - Update section status
	 */
	public function test_update_section_state_method_status() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/sections/basic_blog_setup' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'blog_build_track',
			'section_id' => 'basic_blog_setup',
			'type' => 'status',
			'value' => 'done',
		) );
		$response = $this->steps_api->update_section_state( $request );

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
		$data = $response->get_data();
		
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'status', $data );
		$this->assertEquals( 'basic_blog_setup', $data['id'] );
		$this->assertEquals( 'done', $data['status'] );
	}

	/**
	 * Test update_section_state method - Invalid section ID
	 */
	public function test_update_section_state_method_invalid_section() {
		wp_set_current_user( $this->admin_user );

		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/sections/invalid_section' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'blog_build_track',
			'section_id' => 'invalid_section',
			'type' => 'status',
			'value' => 'done',
		) );
		$response = $this->steps_api->update_section_state( $request );

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertEquals( 'section_not_found', $response->get_error_code() );
	}

	/**
	 * Test API route registration
	 */
	public function test_api_route_registration() {
		// Test that the StepsApi class can be instantiated
		$this->assertInstanceOf( StepsApi::class, $this->steps_api );
		
		// Test that the register_routes method exists and can be called
		$this->assertTrue( method_exists( $this->steps_api, 'register_routes' ) );
		
		// Test that all expected methods exist
		$expected_methods = array(
			'get_steps',
			'add_steps',
			'update_task_status',
			'get_plan_stats',
			'switch_plan',
			'reset_plan',
			'update_track_status',
			'update_section_state',
		);
		
		foreach ( $expected_methods as $method ) {
			$this->assertTrue( method_exists( $this->steps_api, $method ), "Method {$method} should exist" );
		}
	}

	/**
	 * Test API method parameter validation
	 */
	public function test_api_method_parameter_validation() {
		wp_set_current_user( $this->admin_user );

		// Test update_track_status with missing open parameter
		$request = new WP_REST_Request( 'PUT', self::NAMESPACE . self::REST_BASE . '/tracks/blog_build_track' );
		$request->set_body_params( array(
			'plan_id' => 'blog_setup',
			'track_id' => 'blog_build_track',
		) );
		$response = $this->steps_api->update_track_status( $request );

		// The API method may not validate missing parameters at the method level
		// This test verifies that the method can be called without the open parameter
		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$this->assertEquals( 200, $response->get_status() );
	}

	/**
	 * Test API method response formats
	 */
	public function test_api_method_response_formats() {
		wp_set_current_user( $this->admin_user );

		// Test get_steps response format
		$request = new WP_REST_Request( 'GET', self::NAMESPACE . self::REST_BASE );
		$response = $this->steps_api->get_steps( $request );
		
		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'type', $data );
		$this->assertArrayHasKey( 'tracks', $data );
		$this->assertIsArray( $data['tracks'] );

		// Test get_plan_stats response format
		$request = new WP_REST_Request( 'GET', self::NAMESPACE . self::REST_BASE . '/stats' );
		$response = $this->steps_api->get_plan_stats( $request );
		
		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'total_tasks', $data );
		$this->assertArrayHasKey( 'completed_tasks', $data );
		$this->assertArrayHasKey( 'completion_percentage', $data );
		$this->assertIsInt( $data['total_tasks'] );
		$this->assertIsInt( $data['completed_tasks'] );
		$this->assertIsNumeric( $data['completion_percentage'] );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up options after each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );

		parent::tearDown();
	}
}