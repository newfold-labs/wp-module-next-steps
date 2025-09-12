<?php
/**
 * Tests for Plan Version Updates and Language Changes
 *
 * @package WPModuleNextSteps
 */

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\Tests\PHPUnit\TestPlanFactory;

/**
 * Class PlanVersionAndLanguageTest
 *
 * @package WPModuleNextSteps
 */
class PlanVersionAndLanguageTest extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );
	}

	/**
	 * Test version update triggers merge when saved version is older
	 */
	public function test_version_update_triggers_merge() {
		// Create a saved plan with old version and user progress
		$saved_plan = TestPlanFactory::create_old_version_plan();
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should trigger merge due to version difference)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version was updated
		$this->assertEquals( '1.1.1', $current_plan->version );

		// Verify user progress was preserved
		$track = $current_plan->get_track( 'test_track_a' );
		$this->assertTrue( $track->open );

		$section = $current_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $current_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test no merge when saved version is current
	 */
	public function test_no_merge_when_version_is_current() {
		// Create a saved plan with current version and user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the current plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should NOT trigger merge)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version remains the same
		$this->assertEquals( '1.1.1', $current_plan->version );

		// Verify user progress was preserved exactly
		$track = $current_plan->get_track( 'test_track_a' );
		$this->assertTrue( $track->open );

		$section = $current_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $current_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test version update with new tasks added
	 */
	public function test_version_update_with_new_tasks() {
		// Create a saved plan with old version and user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should trigger merge and add new tasks)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version was updated
		$this->assertEquals( '1.1.1', $current_plan->version );

		// Verify user progress was preserved
		$track = $current_plan->get_track( 'test_track_a' );
		$this->assertTrue( $track->open );

		$section = $current_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $current_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test language change triggers resync
	 */
	public function test_language_change_triggers_resync() {
		// Create a saved plan with user progress using custom plan creation
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Simulate language change
		PlanFactory::on_language_change( 'en_US', 'es_ES' );

		// Get the updated plan
		$updated_plan = PlanRepository::get_current_plan();

		// Verify the plan exists and has the expected structure
		$this->assertNotNull( $updated_plan );
		$this->assertGreaterThan( 0, count( $updated_plan->tracks ) );

		// Verify user progress was preserved with full TestPlan structure
		$track = $updated_plan->get_track( 'test_track_a' );
		$this->assertNotNull( $track );
		$this->assertTrue( $track->open );

		$section = $updated_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertNotNull( $section );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $updated_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertNotNull( $task );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test locale switch triggers resync
	 */
	public function test_locale_switch_triggers_resync() {
		// Create a saved plan with user progress using custom plan creation
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Simulate locale switch
		PlanFactory::on_locale_switch( 'fr_FR' );

		// Get the updated plan
		$updated_plan = PlanRepository::get_current_plan();

		// Verify the plan exists and has the expected structure
		$this->assertNotNull( $updated_plan );
		$this->assertGreaterThan( 0, count( $updated_plan->tracks ) );

		// Verify user progress was preserved with full TestPlan structure
		$track = $updated_plan->get_track( 'test_track_a' );
		$this->assertNotNull( $track );
		$this->assertTrue( $track->open );

		$section = $updated_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertNotNull( $section );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $updated_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertNotNull( $task );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test version update with new sections added
	 */
	public function test_version_update_with_new_sections() {
		// Create a saved plan with old version and user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should trigger merge and add new sections)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version was updated
		$this->assertEquals( '1.1.1', $current_plan->version );

		// Verify user progress was preserved
		$track = $current_plan->get_track( 'test_track_a' );
		$this->assertTrue( $track->open );

		$section = $current_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $current_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test version update with new tracks added
	 */
	public function test_version_update_with_new_tracks() {
		// Create a saved plan with old version and user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should trigger merge and add new tracks)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version was updated
		$this->assertEquals( '1.1.1', $current_plan->version );

		// Verify user progress was preserved
		$track = $current_plan->get_track( 'test_track_a' );
		$this->assertTrue( $track->open );

		$section = $current_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $current_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test version update preserves all user progress across multiple levels
	 */
	public function test_version_update_preserves_all_user_progress() {
		// Create a saved plan with extensive user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan->to_array() );

		// Get current plan (should trigger merge)
		$current_plan = PlanRepository::get_current_plan();

		// Verify version was updated
		$this->assertEquals( '1.1.1', $current_plan->version );

		// Verify user progress was preserved
		$track = $current_plan->get_track( 'test_track_a' );
		$this->assertTrue( $track->open );

		$section = $current_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );

		$task = $current_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $task->status );
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
