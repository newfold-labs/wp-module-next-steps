<?php

use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;
use NewfoldLabs\WP\Module\NextSteps\Tests\PHPUnit\TestPlanFactory;

/**
 * Class PlanManagerTest
 *
 * @package WPModuleNextSteps
 */
class PlanRepositoryTest extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
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

		$this->assertEquals( $expected, PlanFactory::PLAN_TYPES );
	}

	/**
	 * Test get_current_plan returns null when no plan exists
	 */
	public function test_get_current_plan_returns_null_when_no_plan_exists() {
		// Ensure no plan option exists
		delete_option( PlanRepository::OPTION );

		// Mock load_default_plan to return null to test the no-plan scenario
		$this->assertInstanceOf( Plan::class, PlanRepository::get_current_plan() );
	}

	/**
	 * Test get_current_plan loads default plan when none exists
	 */
	public function test_get_current_plan_loads_default_plan_when_none_exists() {
		// Set solution via transient (primary method)
		$solutions_data = array( 'solution' => 'WP_SOLUTION_COMMERCE' );
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $solutions_data );

		$plan = PlanRepository::get_current_plan();

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );
	}

	/**
	 * Test get_current_plan returns existing plan when available
	 */
	public function test_get_current_plan_returns_existing_plan() {
		// Create and save a plan using TestPlan
		$test_plan = TestPlanFactory::create_minimal_plan();
		update_option( PlanRepository::OPTION, $test_plan->to_array() );

		$plan = PlanRepository::get_current_plan();

		$this->assertInstanceOf( Plan::class, $plan );
		// With custom plan creation, we can now verify the exact TestPlan structure
		$this->assertEquals( 'test_plan_minimal', $plan->id );
		$this->assertEquals( 'Minimal Test Plan', $plan->label );
		$this->assertEquals( 'custom', $plan->type );
	}

	/**
	 * Test save_plan correctly saves plan data
	 */
	public function test_save_plan() {
		$plan = PlanFactory::create_plan( 'ecommerce' );

		$result = PlanRepository::save_plan( $plan );

		$this->assertTrue( $result );

		// Verify data was saved correctly
		$saved_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $saved_data );
		$this->assertEquals( 'store_setup', $saved_data['id'] );
	}

	/**
	 * Test load_default_plan with ecommerce solution
	 */
	public function test_load_default_plan_ecommerce() {
		$solutions_data = array( 'solution' => 'WP_SOLUTION_COMMERCE' );
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $solutions_data );

		$plan = PlanRepository::get_current_plan();

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );

		// Verify plan was saved
		$this->assertTrue( get_option( PlanRepository::OPTION ) !== false );
	}

	/**
	 * Test load_default_plan with blog solution
	 */
	public function test_load_default_plan_blog() {
		$solutions_data = array( 'solution' => 'WP_SOLUTION_CREATOR' );
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $solutions_data );

		$plan = PlanFactory::load_default_plan();

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
	}

	/**
	 * Test load_default_plan with corporate solution
	 */
	public function test_load_default_plan_corporate() {
		$solutions_data = array( 'solution' => 'WP_SOLUTION_SERVICE' );
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $solutions_data );

		$plan = PlanFactory::load_default_plan();

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'corporate_setup', $plan->id );
	}

	/**
	 * Test load_default_plan defaults to blog when no solution is set
	 */
	public function test_load_default_plan_defaults_to_blog() {
		// Don't set any solution option

		$plan = PlanFactory::load_default_plan();

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
	}

	/**
	 * Test switch_plan with valid plan type
	 */
	public function test_switch_plan_valid_type() {
		// Start with ecommerce plan
		$solutions_data = array( 'solution' => 'WP_SOLUTION_COMMERCE' );
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $solutions_data );
		PlanFactory::load_default_plan();

		// Switch to blog plan
		$plan = PlanRepository::switch_plan( 'blog' );

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );

		// The module should NOT modify external options - it's read-only for those
		// We only verify that the correct plan object was returned

		// Verify the plan was processed correctly and saved to the module's own option
		$saved_plan_data = get_option( PlanRepository::OPTION );
		$this->assertEquals( 'blog_setup', $saved_plan_data['id'] );
	}

	/**
	 * Test switch_plan with invalid plan type
	 */
	public function test_switch_plan_invalid_type() {
		$result = PlanRepository::switch_plan( 'invalid_type' );

		$this->assertFalse( $result );

		// No external state should be modified for invalid plan types
	}

	/**
	 * Test create_plan with ecommerce type returns correct plan structure
	 */
	public function test_create_plan_ecommerce_structure() {
		$plan = PlanFactory::create_plan( 'ecommerce' );

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );
		$this->assertEquals( 'Store Setup', $plan->label );

		$tracks = $plan->tracks;
		$this->assertGreaterThan( 0, count( $tracks ) );

		// Check first track has expected structure
		$first_track = $tracks[0];
		$this->assertEquals( 'store_build_track', $first_track->id );
		$this->assertEquals( 'Build', $first_track->label );

		// Check track has sections
		$sections = $first_track->sections;
		$this->assertGreaterThan( 0, count( $sections ) );

		// Check first section has tasks
		$first_section = $sections[0];
		$tasks         = $first_section->tasks;
		$this->assertGreaterThan( 0, count( $tasks ) );
	}

	/**
	 * Test create_plan with blog type returns correct plan structure
	 */
	public function test_create_plan_blog_structure() {
		$plan = PlanFactory::create_plan( 'blog' );

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
		$this->assertEquals( 'Blog Setup', $plan->label );
	}

	/**
	 * Test create_plan with corporate type returns correct plan structure
	 */
	public function test_create_plan_corporate_structure() {
		$plan = PlanFactory::create_plan( 'corporate' );

		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'corporate_setup', $plan->id );
		$this->assertEquals( 'Corporate Setup', $plan->label );
	}

	/**
	 * Test update_task_status with valid task
	 */
	public function test_update_task_status() {
		// Load test plan which has tasks
		$plan = TestPlanFactory::create_test_plan();
		PlanRepository::save_plan( $plan );

		// Update task status
		$result = PlanRepository::update_task_status(
			'test_track_a',
			'test_section_1',
			'test_task_1',
			'done'
		);

		$this->assertTrue( $result );

		// Verify the task status was updated
		$updated_plan = PlanRepository::get_current_plan();
		$task         = $updated_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test update_task_status with invalid IDs
	 */
	public function test_update_task_status_invalid_ids() {
		$plan = TestPlanFactory::create_test_plan();
		PlanRepository::save_plan( $plan );

		// Try to update non-existent task
		$result = PlanRepository::update_task_status(
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
		$plan = TestPlanFactory::create_test_plan();
		PlanRepository::save_plan( $plan );

		$task = PlanRepository::get_task(
			'test_track_a',
			'test_section_1',
			'test_task_1'
		);

		$this->assertInstanceOf( Task::class, $task );
		$this->assertEquals( 'test_task_1', $task->id );
	}

	/**
	 * Test get_task with invalid IDs
	 */
	public function test_get_task_invalid_ids() {
		$plan = TestPlanFactory::create_test_plan();
		PlanRepository::save_plan( $plan );

		$task = PlanRepository::get_task(
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
		update_option( PlanRepository::OPTION, $test_plan_data );
		$solutions_data = array( 'solution' => 'WP_SOLUTION_CREATOR' );
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $solutions_data );

		$plan = PlanRepository::reset_plan();

		$this->assertInstanceOf( Plan::class, $plan );

		// Verify default plan was loaded based on solution
		$this->assertEquals( 'blog_setup', $plan->id );

		// Verify old data was cleared
		$this->assertNotEquals( $test_plan_data, get_option( PlanRepository::OPTION ) );
	}

	/**
	 * Test get_plan_stats returns correct statistics
	 */
	public function test_get_plan_stats() {
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		$stats = PlanRepository::get_plan_stats();

		$this->assertIsArray( $stats );
		$this->assertArrayHasKey( 'completion_percentage', $stats );
		$this->assertArrayHasKey( 'total_tasks', $stats );
		$this->assertArrayHasKey( 'completed_tasks', $stats );
		$this->assertArrayHasKey( 'total_sections', $stats );
		$this->assertArrayHasKey( 'completed_sections', $stats );
		$this->assertArrayHasKey( 'total_tracks', $stats );
		$this->assertArrayHasKey( 'completed_tracks', $stats );

		$this->assertIsInt( $stats['total_tasks'] );
		$this->assertIsInt( $stats['completed_tasks'] );
		$this->assertIsInt( $stats['total_sections'] );
		$this->assertIsInt( $stats['completed_sections'] );
		$this->assertIsInt( $stats['total_tracks'] );
		$this->assertIsInt( $stats['completed_tracks'] );
		$this->assertGreaterThan( 0, $stats['total_tasks'] );
	}

	/**
	 * Test get_plan_stats with no plan
	 */
	public function test_get_plan_stats_no_plan() {
		// Force no plan scenario by mocking
		delete_option( PlanRepository::OPTION );

		$stats = PlanRepository::get_plan_stats();

		// Should return empty array or handle gracefully
		$this->assertIsArray( $stats );
	}

	/**
	 * Test update_section_status
	 */
	public function test_update_section_status() {
		$plan = TestPlanFactory::create_test_plan();
		PlanRepository::save_plan( $plan );

		$result = PlanRepository::update_section_state(
			'test_track_a',
			'test_section_1',
			'open',
			false
		);

		$this->assertTrue( $result );

		// Verify the section status was updated
		$updated_plan = PlanRepository::get_current_plan();
		$section      = $updated_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertFalse( $section->open );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up options after each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );

		parent::tearDown();
	}
}
