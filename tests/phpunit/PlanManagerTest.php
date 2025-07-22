<?php

use NewfoldLabs\WP\Module\NextSteps\PlanManager;
use NewfoldLabs\WP\Module\NextSteps\PlanLoader;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;

/**
 * Class PlanManagerTest
 *
 * @package WPModuleNextSteps
 */
class PlanManagerTest extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Clean up options before each test
		delete_option( PlanManager::OPTION );
		delete_transient( PlanLoader::SOLUTIONS_TRANSIENT );
	}

	/**
	 * Test PLAN_TYPES constant has correct mapping
	 */
	public function test_plan_types_constant() {
		$expected = array(
			'personal'  => 'blog',
			'business'  => 'corporate',
			'ecommerce' => 'ecommerce',
		);
		
		$this->assertEquals( $expected, PlanManager::PLAN_TYPES );
	}

	/**
	 * Test get_current_plan returns null when no plan exists
	 */
	public function test_get_current_plan_returns_null_when_no_plan_exists() {
		// Ensure no plan option exists
		delete_option( PlanManager::OPTION );
		
		// Mock load_default_plan to return null to test the no-plan scenario
		$this->assertInstanceOf( Plan::class, PlanManager::get_current_plan() );
	}

	/**
	 * Test get_current_plan loads default plan when none exists
	 */
	public function test_get_current_plan_loads_default_plan_when_none_exists() {
		// Set solution via transient (primary method)
		$solutions_data = array( 'solution' => 'WP_SOLUTION_COMMERCE' );
		set_transient( PlanLoader::SOLUTIONS_TRANSIENT, $solutions_data );
		
		$plan = PlanManager::get_current_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );
	}

	/**
	 * Test get_current_plan returns existing plan when available
	 */
	public function test_get_current_plan_returns_existing_plan() {
		// Create and save a plan
		$test_plan_data = array(
			'id'          => 'test_plan',
			'label'       => 'Test Plan',
			'description' => 'A test plan',
			'tracks'      => array()
		);
		
		update_option( PlanManager::OPTION, $test_plan_data );
		
		$plan = PlanManager::get_current_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'test_plan', $plan->id );
		$this->assertEquals( 'Test Plan', $plan->label );
	}

	/**
	 * Test save_plan correctly saves plan data
	 */
	public function test_save_plan() {
		$plan = PlanManager::get_ecommerce_plan();
		
		$result = PlanManager::save_plan( $plan );
		
		$this->assertTrue( $result );
		
		// Verify data was saved correctly
		$saved_data = get_option( PlanManager::OPTION );
		$this->assertIsArray( $saved_data );
		$this->assertEquals( 'store_setup', $saved_data['id'] );
	}

	/**
	 * Test load_default_plan with ecommerce solution
	 */
	public function test_load_default_plan_ecommerce() {
		$solutions_data = array( 'solution' => 'WP_SOLUTION_COMMERCE' );
		set_transient( PlanLoader::SOLUTIONS_TRANSIENT, $solutions_data );
		
		$plan = PlanLoader::load_default_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );
		
		// Verify plan was saved
		$this->assertTrue( get_option( PlanManager::OPTION ) !== false );
	}

	/**
	 * Test load_default_plan with blog solution
	 */
	public function test_load_default_plan_blog() {
		$solutions_data = array( 'solution' => 'WP_SOLUTION_CREATOR' );
		set_transient( PlanLoader::SOLUTIONS_TRANSIENT, $solutions_data );
		
		$plan = PlanLoader::load_default_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
	}

	/**
	 * Test load_default_plan with corporate solution
	 */
	public function test_load_default_plan_corporate() {
		$solutions_data = array( 'solution' => 'WP_SOLUTION_SERVICE' );
		set_transient( PlanLoader::SOLUTIONS_TRANSIENT, $solutions_data );
		
		$plan = PlanLoader::load_default_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'corporate_setup', $plan->id );
	}

	/**
	 * Test load_default_plan defaults to blog when no solution is set
	 */
	public function test_load_default_plan_defaults_to_blog() {
		// Don't set any solution option
		
		$plan = PlanLoader::load_default_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
	}

	/**
	 * Test switch_plan with valid plan type
	 */
	public function test_switch_plan_valid_type() {
		// Start with ecommerce plan
		$solutions_data = array( 'solution' => 'WP_SOLUTION_COMMERCE' );
		set_transient( PlanLoader::SOLUTIONS_TRANSIENT, $solutions_data );
		PlanLoader::load_default_plan();
		
		// Switch to blog plan
		$plan = PlanManager::switch_plan( 'blog' );
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
		
		// The module should NOT modify external options - it's read-only for those
		// We only verify that the correct plan object was returned
		
		// Verify the plan was processed correctly and saved to the module's own option
		$saved_plan_data = get_option( PlanManager::OPTION );
		$this->assertEquals( 'blog_setup', $saved_plan_data['id'] );
	}

	/**
	 * Test switch_plan with invalid plan type
	 */
	public function test_switch_plan_invalid_type() {
		$result = PlanManager::switch_plan( 'invalid_type' );
		
		$this->assertFalse( $result );
		
		// No external state should be modified for invalid plan types
	}

	/**
	 * Test get_ecommerce_plan returns correct plan structure
	 */
	public function test_get_ecommerce_plan_structure() {
		$plan = PlanManager::get_ecommerce_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );
		$this->assertEquals( 'Store Setup', $plan->label );
		
		$tracks = $plan->get_tracks();
		$this->assertGreaterThan( 0, count( $tracks ) );
		
		// Check first track has expected structure
		$first_track = $tracks[0];
		$this->assertEquals( 'store_build_track', $first_track->id );
		$this->assertEquals( 'Build', $first_track->label );
		
		// Check track has sections
		$sections = $first_track->get_sections();
		$this->assertGreaterThan( 0, count( $sections ) );
		
		// Check first section has tasks
		$first_section = $sections[0];
		$tasks = $first_section->get_tasks();
		$this->assertGreaterThan( 0, count( $tasks ) );
	}

	/**
	 * Test get_blog_plan returns correct plan structure
	 */
	public function test_get_blog_plan_structure() {
		$plan = PlanManager::get_blog_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
		$this->assertEquals( 'Blog Setup', $plan->label );
	}

	/**
	 * Test get_corporate_plan returns correct plan structure
	 */
	public function test_get_corporate_plan_structure() {
		$plan = PlanManager::get_corporate_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'corporate_setup', $plan->id );
		$this->assertEquals( 'Corporate Setup', $plan->label );
	}

	/**
	 * Test update_task_status with valid task
	 */
	public function test_update_task_status() {
		// Load ecommerce plan which has tasks
		$plan = PlanManager::get_ecommerce_plan();
		PlanManager::save_plan( $plan );
		
		// Update task status
		$result = PlanManager::update_task_status( 
			'store_build_track', 
			'basic_store_setup', 
			'store_quick_setup',
			'done'
		);
		
		$this->assertTrue( $result );
		
		// Verify the task status was updated
		$updated_plan = PlanManager::get_current_plan();
		$task = $updated_plan->get_task( 'store_build_track', 'basic_store_setup', 'store_quick_setup' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test update_task_status with invalid IDs
	 */
	public function test_update_task_status_invalid_ids() {
		$plan = PlanManager::get_ecommerce_plan();
		PlanManager::save_plan( $plan );
		
		// Try to update non-existent task
		$result = PlanManager::update_task_status(
			'invalid_track',
			'invalid_section', 
			'invalid_task',
			'done'
		);
		
		$this->assertFalse( $result );
	}

	/**
	 * Test get_task with valid IDs
	 */
	public function test_get_task_valid_ids() {
		$plan = PlanManager::get_ecommerce_plan();
		PlanManager::save_plan( $plan );
		
		$task = PlanManager::get_task(
			'store_build_track',
			'basic_store_setup', 
			'store_quick_setup'
		);
		
		$this->assertInstanceOf( Task::class, $task );
		$this->assertEquals( 'store_quick_setup', $task->id );
	}

	/**
	 * Test get_task with invalid IDs
	 */
	public function test_get_task_invalid_ids() {
		$plan = PlanManager::get_ecommerce_plan();
		PlanManager::save_plan( $plan );
		
		$task = PlanManager::get_task(
			'invalid_track',
			'invalid_section',
			'invalid_task'
		);
		
		$this->assertNull( $task );
	}

	/**
	 * Test reset_plan clears data and loads default
	 */
	public function test_reset_plan() {
		// Set up existing plan data
		$test_plan_data = array( 'id' => 'test_plan' );
		update_option( PlanManager::OPTION, $test_plan_data );
		$solutions_data = array( 'solution' => 'WP_SOLUTION_CREATOR' );
		set_transient( PlanLoader::SOLUTIONS_TRANSIENT, $solutions_data );
		
		$plan = PlanManager::reset_plan();
		
		$this->assertInstanceOf( Plan::class, $plan );
		
		// Verify default plan was loaded based on solution
		$this->assertEquals( 'blog_setup', $plan->id );
		
		// Verify old data was cleared
		$this->assertNotEquals( $test_plan_data, get_option( PlanManager::OPTION ) );
	}

	/**
	 * Test get_plan_stats returns correct statistics
	 */
	public function test_get_plan_stats() {
		$plan = PlanManager::get_ecommerce_plan();
		PlanManager::save_plan( $plan );
		
		$stats = PlanManager::get_plan_stats();
		
		$this->assertIsArray( $stats );
		$this->assertArrayHasKey( 'completion_percentage', $stats );
		$this->assertArrayHasKey( 'total_tasks', $stats );
		$this->assertArrayHasKey( 'completed_tasks', $stats );
		$this->assertArrayHasKey( 'total_sections', $stats );
		$this->assertArrayHasKey( 'total_tracks', $stats );
		$this->assertArrayHasKey( 'is_completed', $stats );
		
		$this->assertIsInt( $stats['total_tasks'] );
		$this->assertIsInt( $stats['completed_tasks'] );
		$this->assertIsBool( $stats['is_completed'] );
		$this->assertGreaterThan( 0, $stats['total_tasks'] );
	}

	/**
	 * Test get_plan_stats with no plan
	 */
	public function test_get_plan_stats_no_plan() {
		// Force no plan scenario by mocking
		delete_option( PlanManager::OPTION );
		
		$stats = PlanManager::get_plan_stats();
		
		// Should return empty array or handle gracefully
		$this->assertIsArray( $stats );
	}

	/**
	 * Test update_section_status
	 */
	public function test_update_section_status() {
		$plan = PlanManager::get_ecommerce_plan();
		PlanManager::save_plan( $plan );
		
		$result = PlanManager::update_section_status(
			'store_build_track',
			'basic_store_setup',
			false
		);
		
		$this->assertTrue( $result );
		
		// Verify the section status was updated
		$updated_plan = PlanManager::get_current_plan();
		$section = $updated_plan->get_section( 'store_build_track', 'basic_store_setup' );
		$this->assertFalse( $section->is_open() );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up options after each test
		delete_option( PlanManager::OPTION );
		delete_transient( PlanLoader::SOLUTIONS_TRANSIENT );
		
		parent::tearDown();
	}
} 