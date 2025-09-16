<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Track;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;
use NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit\TestPlanFactory;

/**
 * WordPress Unit Tests for Plan Merge Functionality
 * 
 * These tests run in a real WordPress environment with database access.
 * They test the actual integration with WordPress functions and database.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\DTOs\Plan
 */
class PlanMergeWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Load TestPlanFactory from wpunit directory
		require_once dirname( __DIR__ ) . '/wpunit/TestPlanFactory.php';

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		
		// Invalidate static cache
		PlanRepository::invalidate_cache();
	}

	/**
	 * Test basic plan merge functionality
	 */
	public function test_basic_plan_merge() {
		// Create a new plan (current version)
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with some user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Merge the plans
		$merged_plan = $new_plan->merge_with( $saved_plan );

		// Verify the merge preserved user progress
		$this->assertEquals( 'test_plan', $merged_plan->id );
		$this->assertEquals( 'custom', $merged_plan->type );

		// Check that user progress was preserved
		$first_track = $merged_plan->tracks[0];
		$this->assertTrue( $first_track->open ); // User opened this track

		$first_section = $first_track->sections[0];
		$this->assertEquals( 'completed', $first_section->status ); // User completed this section
		$this->assertNotEmpty( $first_section->date_completed );

		$first_task = $first_section->tasks[0];
		$this->assertEquals( 'done', $first_task->status ); // User completed this task
	}

	/**
	 * Test plan merge with new tracks
	 */
	public function test_plan_merge_with_new_tracks() {
		// Create a new plan with additional tracks
		$new_plan = TestPlanFactory::create_plan_with_track_modifications( 'test_track_a', array() );

		// Create a saved plan with progress on existing tracks
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Merge the plans
		$merged_plan = $new_plan->merge_with( $saved_plan );

		// Verify new tracks are included
		$this->assertCount( 2, $merged_plan->tracks );

		// Verify existing track progress is preserved
		$existing_track = $merged_plan->tracks[0];
		$this->assertTrue( $existing_track->open ); // User progress preserved

		// Verify new track has default state
		$new_track = $merged_plan->tracks[1];
		$this->assertFalse( $new_track->open ); // Default state for new track
	}

	/**
	 * Test plan merge with new sections
	 */
	public function test_plan_merge_with_new_sections() {
		// Create a new plan with additional sections
		$new_plan = TestPlanFactory::create_plan_with_section_modifications( 'test_track_a', 'new_section', array() );

		// Create a saved plan with progress on existing sections
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Merge the plans
		$merged_plan = $new_plan->merge_with( $saved_plan );

		// Verify new sections are included
		$first_track = $merged_plan->tracks[0];
		$this->assertCount( 2, $first_track->sections );

		// Verify existing section progress is preserved
		$existing_section = $first_track->sections[0];
		$this->assertEquals( 'completed', $existing_section->status ); // User progress preserved

		// Verify new section has default state
		$new_section = $first_track->sections[1];
		$this->assertEquals( 'in_progress', $new_section->status ); // Default state for new section
	}

	/**
	 * Test plan merge with new tasks
	 */
	public function test_plan_merge_with_new_tasks() {
		// Create a new plan with additional tasks
		$new_plan = TestPlanFactory::create_plan_with_task_modifications( 'test_track_a', 'test_section_1', 'new_task', array() );

		// Create a saved plan with progress on existing tasks
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Merge the plans
		$merged_plan = $new_plan->merge_with( $saved_plan );

		// Verify new tasks are included
		$first_track = $merged_plan->tracks[0];
		$first_section = $first_track->sections[0];
		$this->assertCount( 2, $first_section->tasks );

		// Verify existing task progress is preserved
		$existing_task = $first_section->tasks[0];
		$this->assertEquals( 'done', $existing_task->status ); // User progress preserved

		// Verify new task has default state
		$new_task = $first_section->tasks[1];
		$this->assertEquals( 'done', $new_task->status ); // Default state for new task
	}

	/**
	 * Test plan merge preserves plan ID and type
	 */
	public function test_plan_merge_preserves_plan_id_and_type() {
		// Create a new plan
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with different ID and type
		$saved_plan = TestPlanFactory::create_plan_with_progress();
		$saved_plan->id = 'saved_plan_id';
		$saved_plan->type = 'saved_type';

		// Merge the plans
		$merged_plan = $new_plan->merge_with( $saved_plan );

		// Verify plan ID and type are preserved from saved plan
		$this->assertEquals( 'saved_plan_id', $merged_plan->id );
		$this->assertEquals( 'saved_type', $merged_plan->type );
	}

	/**
	 * Test plan merge with empty saved plan
	 */
	public function test_plan_merge_with_empty_saved_plan() {
		// Create a new plan
		$new_plan = TestPlanFactory::create_test_plan();

		// Create an empty saved plan
		$saved_plan = new Plan( array() );

		// Merge the plans
		$merged_plan = $new_plan->merge_with( $saved_plan );

		// Verify the new plan structure is preserved
		$this->assertEquals( '', $merged_plan->id ); // Empty saved plan has no ID
		$this->assertEquals( '', $merged_plan->type ); // Empty saved plan has no type
		$this->assertCount( 2, $merged_plan->tracks ); // Test plan has 2 tracks
	}

	/**
	 * Test track merge functionality
	 */
	public function test_track_merge() {
		// Create a new track
		$new_track = new Track( array(
			'id'          => 'test_track',
			'label'       => 'Test Track',
			'description' => 'Test Description',
			'open'        => true,
			'sections'    => array(),
		) );

		// Create a saved track with user progress
		$saved_track = new Track( array(
			'id'          => 'test_track',
			'label'       => 'Saved Track',
			'description' => 'Saved Description',
			'open'        => false, // User closed this track
			'sections'    => array(),
		) );

		// Merge the tracks
		$merged_track = $new_track->merge_with( $saved_track );

		// Verify user progress is preserved
		$this->assertEquals( 'test_track', $merged_track->id );
		$this->assertEquals( 'Test Track', $merged_track->label ); // New label preserved
		$this->assertEquals( 'Test Description', $merged_track->description ); // New description preserved
		$this->assertFalse( $merged_track->open ); // User progress preserved
	}

	/**
	 * Test section merge functionality
	 */
	public function test_section_merge() {
		// Create a new section
		$new_section = new Section( array(
			'id'          => 'test_section',
			'label'       => 'Test Section',
			'description' => 'Test Description',
			'open'        => true,
			'status'      => 'new',
			'tasks'       => array(),
		) );

		// Create a saved section with user progress
		$saved_section = new Section( array(
			'id'             => 'test_section',
			'label'          => 'Saved Section',
			'description'    => 'Saved Description',
			'open'           => false, // User closed this section
			'status'         => 'done', // User completed this section
			'date_completed' => '2023-01-01 12:00:00',
			'tasks'          => array(),
		) );

		// Merge the sections
		$merged_section = $new_section->merge_with( $saved_section );

		// Verify user progress is preserved
		$this->assertEquals( 'test_section', $merged_section->id );
		$this->assertEquals( 'Test Section', $merged_section->label ); // New label preserved
		$this->assertEquals( 'Test Description', $merged_section->description ); // New description preserved
		$this->assertFalse( $merged_section->open ); // User progress preserved
		$this->assertEquals( 'done', $merged_section->status ); // User progress preserved
		$this->assertEquals( '2023-01-01 12:00:00', $merged_section->date_completed ); // User progress preserved
	}

	/**
	 * Test task merge functionality
	 */
	public function test_task_merge() {
		// Create a new task
		$new_task = new Task( array(
			'id'    => 'test_task',
			'title' => 'Test Task',
			'status' => 'new',
		) );

		// Create a saved task with user progress
		$saved_task = new Task( array(
			'id'    => 'test_task',
			'title' => 'Saved Task',
			'status' => 'done', // User completed this task
		) );

		// Merge the tasks
		$merged_task = $new_task->merge_with( $saved_task );

		// Verify user progress is preserved
		$this->assertEquals( 'test_task', $merged_task->id );
		$this->assertEquals( 'Test Task', $merged_task->title ); // New title preserved
		$this->assertEquals( 'done', $merged_task->status ); // User progress preserved
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
