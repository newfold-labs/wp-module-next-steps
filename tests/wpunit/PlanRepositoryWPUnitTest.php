<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit\TestPlanFactory;

/**
 * WordPress Unit Tests for PlanRepository
 * 
 * These tests run in a real WordPress environment with database access.
 * They test the actual integration with WordPress functions and database.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\PlanRepository
 */
class PlanRepositoryWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Load TestPlanFactory from phpunit directory
		require_once dirname( __DIR__ ) . '/wpunit/TestPlanFactory.php';

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		
		// Invalidate static cache
		PlanRepository::invalidate_cache();
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
	 * Note: In real WordPress environment, this will load default plan instead of returning null
	 */
	public function test_get_current_plan_loads_default_when_no_plan_exists() {
		// Ensure no plan option exists
		delete_option( PlanRepository::OPTION );

		// In real WordPress environment, this will load the default blog plan
		$plan = PlanRepository::get_current_plan();
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
	}

	/**
	 * Test get_current_plan loads default plan when none exists
	 */
	public function test_get_current_plan_loads_default_plan_when_none_exists() {
		// Ensure no plan option exists
		delete_option( PlanRepository::OPTION );

		// This should load the default blog plan
		$plan = PlanRepository::get_current_plan();
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
	}

	/**
	 * Test get_current_plan returns existing plan
	 */
	public function test_get_current_plan_returns_existing_plan() {
		// Set up a test plan
		$test_plan = TestPlanFactory::create_minimal_plan();
		$saved = PlanRepository::save_plan( $test_plan );
		$this->assertTrue( $saved );

		// Retrieve the plan
		$retrieved_plan = PlanRepository::get_current_plan();
		$this->assertInstanceOf( Plan::class, $retrieved_plan );
		$this->assertEquals( 'test_plan_minimal', $retrieved_plan->id );
		$this->assertEquals( 'Minimal Test Plan', $retrieved_plan->label );
	}

	/**
	 * Test save_plan saves plan data
	 */
	public function test_save_plan() {
		// Create a test plan
		$test_plan = TestPlanFactory::create_minimal_plan();
		$test_plan->type = 'ecommerce'; // Change type for this test
		$test_plan->id = 'store_setup'; // Change ID for this test

		// Save the plan
		$result = PlanRepository::save_plan( $test_plan );
		$this->assertTrue( $result );

		// Verify it was saved
		$saved_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $saved_data );
		$this->assertEquals( 'store_setup', $saved_data['id'] );
		$this->assertEquals( 'ecommerce', $saved_data['type'] );
	}

	/**
	 * Test switch_plan creates new plan
	 */
	public function test_switch_plan() {
		// Switch to ecommerce plan
		$plan = PlanRepository::switch_plan( 'ecommerce' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'ecommerce', $plan->type );
		$this->assertEquals( 'store_setup', $plan->id );

		// Verify it was saved
		$saved_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'ecommerce', $saved_plan->type );
	}

	/**
	 * Test reset_plan resets to default plan
	 */
	public function test_reset_plan() {
		// First, save a custom plan
		$test_plan = TestPlanFactory::create_minimal_plan();
		$test_plan->id = 'custom_plan';
		$test_plan->type = 'custom';
		PlanRepository::save_plan( $test_plan );

		// Verify custom plan exists
		$current_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'custom_plan', $current_plan->id );
		$this->assertEquals( 'custom', $current_plan->type );

		// Reset the plan
		$result = PlanRepository::reset_plan();
		$this->assertInstanceOf( Plan::class, $result );

		// Verify it's reset to default blog plan
		$reset_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog_setup', $reset_plan->id );
		$this->assertEquals( 'blog', $reset_plan->type );
	}

	/**
	 * Test cache invalidation works
	 */
	public function test_cache_invalidation() {
		// Save a plan
		$test_plan = TestPlanFactory::create_minimal_plan();
		PlanRepository::save_plan( $test_plan );

		// Get the plan (should be cached)
		$plan1 = PlanRepository::get_current_plan();
		$this->assertInstanceOf( Plan::class, $plan1 );

		// Invalidate cache
		PlanRepository::invalidate_cache();

		// Delete the option directly
		delete_option( PlanRepository::OPTION );

		// Get the plan again (should load default plan since cache was invalidated and option deleted)
		$plan2 = PlanRepository::get_current_plan();
		$this->assertInstanceOf( Plan::class, $plan2 );
		$this->assertEquals( 'blog_setup', $plan2->id ); // Should load default blog plan
	}

	/**
	 * Test load_default_plan for ecommerce
	 */
	public function test_load_default_plan_ecommerce() {
		$plan = PlanFactory::create_plan( 'ecommerce' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );
		$this->assertEquals( 'ecommerce', $plan->type );
	}

	/**
	 * Test load_default_plan for blog
	 */
	public function test_load_default_plan_blog() {
		$plan = PlanFactory::create_plan( 'blog' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
		$this->assertEquals( 'blog', $plan->type );
	}

	/**
	 * Test load_default_plan for corporate
	 */
	public function test_load_default_plan_corporate() {
		$plan = PlanFactory::create_plan( 'corporate' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'corporate_setup', $plan->id );
		$this->assertEquals( 'corporate', $plan->type );
	}

	/**
	 * Test load_default_plan defaults to blog
	 */
	public function test_load_default_plan_defaults_to_blog() {
		$plan = PlanFactory::create_plan( 'invalid_type' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
		$this->assertEquals( 'blog', $plan->type );
	}

	/**
	 * Test switch_plan with invalid type
	 */
	public function test_switch_plan_invalid_type() {
		$plan = PlanRepository::switch_plan( 'invalid_type' );
		$this->assertFalse( $plan );
	}

	/**
	 * Test create_plan ecommerce structure
	 */
	public function test_create_plan_ecommerce_structure() {
		$plan = PlanFactory::create_plan( 'ecommerce' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'store_setup', $plan->id );
		$this->assertEquals( 'ecommerce', $plan->type );
		$this->assertNotEmpty( $plan->tracks );
	}

	/**
	 * Test create_plan blog structure
	 */
	public function test_create_plan_blog_structure() {
		$plan = PlanFactory::create_plan( 'blog' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'blog_setup', $plan->id );
		$this->assertEquals( 'blog', $plan->type );
		$this->assertNotEmpty( $plan->tracks );
	}

	/**
	 * Test create_plan corporate structure
	 */
	public function test_create_plan_corporate_structure() {
		$plan = PlanFactory::create_plan( 'corporate' );
		$this->assertInstanceOf( Plan::class, $plan );
		$this->assertEquals( 'corporate_setup', $plan->id );
		$this->assertEquals( 'corporate', $plan->type );
		$this->assertNotEmpty( $plan->tracks );
	}

	/**
	 * Test update_task_status
	 */
	public function test_update_task_status() {
		// Create and save a plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Update a task status
		$result = PlanRepository::update_task_status( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup', 'done' );
		$this->assertTrue( $result );

		// Verify the task was updated
		$updated_plan = PlanRepository::get_current_plan();
		$task = PlanRepository::get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertInstanceOf( \NewfoldLabs\WP\Module\NextSteps\DTOs\Task::class, $task );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test update_task_status with invalid IDs
	 */
	public function test_update_task_status_invalid_ids() {
		// Create and save a plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Try to update with invalid IDs
		$result = PlanRepository::update_task_status( 'invalid_track', 'invalid_section', 'invalid_task', 'done' );
		$this->assertFalse( $result );
	}

	/**
	 * Test get_task with valid IDs
	 */
	public function test_get_task_valid_ids() {
		// Create and save a plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Get a task
		$task = PlanRepository::get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertInstanceOf( \NewfoldLabs\WP\Module\NextSteps\DTOs\Task::class, $task );
		$this->assertEquals( 'blog_quick_setup', $task->id );
	}

	/**
	 * Test get_task with invalid IDs
	 */
	public function test_get_task_invalid_ids() {
		// Create and save a plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Try to get with invalid IDs
		$task = PlanRepository::get_task( 'invalid_track', 'invalid_section', 'invalid_task' );
		$this->assertNull( $task );
	}

	/**
	 * Test get_plan_stats
	 */
	public function test_get_plan_stats() {
		// Create and save a plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Get plan stats
		$stats = PlanRepository::get_plan_stats();
		$this->assertIsArray( $stats );
		$this->assertArrayHasKey( 'total_tasks', $stats );
		$this->assertArrayHasKey( 'completed_tasks', $stats );
		$this->assertArrayHasKey( 'completion_percentage', $stats );
	}

	/**
	 * Test get_plan_stats with no plan
	 */
	public function test_get_plan_stats_no_plan() {
		// Ensure no plan exists
		delete_option( PlanRepository::OPTION );

		// Get plan stats (should return default blog plan stats)
		$stats = PlanRepository::get_plan_stats();
		$this->assertIsArray( $stats );
		$this->assertGreaterThan( 0, $stats['total_tasks'] ); // Default plan has tasks
		$this->assertEquals( 0, $stats['completed_tasks'] ); // No completed tasks
		$this->assertEquals( 0, $stats['completion_percentage'] ); // 0% completion
	}

}
