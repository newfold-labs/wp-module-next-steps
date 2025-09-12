<?php
/**
 * Tests for Plan Merge Functionality
 *
 * @package WPModuleNextSteps
 */

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Track;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;
use NewfoldLabs\WP\Module\NextSteps\Tests\PHPUnit\TestPlanFactory;

/**
 * Class PlanMergeTest
 *
 * @package WPModuleNextSteps
 */
class PlanMergeTest extends WP_UnitTestCase {

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
	 * Test basic plan merge functionality
	 */
	public function test_basic_plan_merge() {
		// Create a new plan (current version)
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with some user progress
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify the merge preserved user progress
		$this->assertEquals( 'test_plan', $merged_plan->id );
		$this->assertEquals( 'custom', $merged_plan->type );

		// Check that track open state was preserved
		$merged_track = $merged_plan->get_track( 'test_track_a' );
		$this->assertTrue( $merged_track->open );

		// Check that section state was preserved
		$merged_section = $merged_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $merged_section->open );
		$this->assertEquals( 'completed', $merged_section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $merged_section->date_completed );

		// Check that task status was preserved
		$merged_task = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $merged_task->status );

		// Check that version was updated to current version
		$this->assertEquals( '1.1.1', $merged_plan->version );
	}

	/**
	 * Test merge with version update scenario
	 */
	public function test_merge_with_version_update() {
		// Create a new plan (current version)
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with old version and user progress
		$saved_plan = TestPlanFactory::create_old_version_plan();
		$saved_plan = TestPlanFactory::create_plan_with_progress();

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify version was updated
		$this->assertEquals( '1.1.1', $merged_plan->version );

		// Verify user progress was preserved
		$merged_track = $merged_plan->get_track( 'test_track_a' );
		$this->assertTrue( $merged_track->open );

		$merged_section = $merged_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertNotNull( $merged_section );
		$this->assertTrue( $merged_section->open );
		$this->assertEquals( 'completed', $merged_section->status );

		$merged_task1 = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $merged_task1->status );

		$merged_task2 = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_2' );
		$this->assertNotNull( $merged_task2 );
		$this->assertEquals( 'done', $merged_task2->status );
	}

	/**
	 * Test merge with new tasks added in updated plan
	 */
	public function test_merge_with_new_tasks() {
		// Create a new plan (current version)
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with only one task in section 1
		$saved_plan = TestPlanFactory::create_plan_with_task_modifications(
			'test_track_a',
			'test_section_1',
			'test_task_1',
			array(
				'status' => 'done',
			)
		);

		// Remove the second task to simulate an old plan structure
		$saved_plan_data = $saved_plan->to_array();
		unset( $saved_plan_data['tracks'][0]['sections'][0]['tasks'][1] );
		$saved_plan = new Plan( $saved_plan_data );

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify existing task status was preserved
		$existing_task = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertNotNull( $existing_task );
		$this->assertEquals( 'done', $existing_task->status );

		// Verify new tasks from updated plan are present with default status
		$new_tasks = $merged_plan->get_section( 'test_track_a', 'test_section_1' )->tasks;
		$this->assertGreaterThanOrEqual( 1, count( $new_tasks ) );

		// Verify that the existing task has the correct status
		$existing_task = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertEquals( 'done', $existing_task->status );

		// If there are other tasks, they should have default status
		foreach ( $new_tasks as $task ) {
			if ( 'test_task_1' !== $task->id ) {
				$this->assertEquals( 'new', $task->status );
			}
		}
	}

	/**
	 * Test merge with new sections added in updated plan
	 */
	public function test_merge_with_new_sections() {
		// Create a new plan (current version)
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with only one section in track A
		$saved_plan = TestPlanFactory::create_plan_with_section_modifications(
			'test_track_a',
			'test_section_1',
			array(
				'open'           => true,
				'status'         => 'completed',
				'date_completed' => '2024-01-01 12:00:00',
			)
		);

		// Remove the second section to simulate an old plan structure
		$saved_plan_data = $saved_plan->to_array();
		unset( $saved_plan_data['tracks'][0]['sections'][1] );
		$saved_plan = new Plan( $saved_plan_data );

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify existing section state was preserved
		$existing_section = $merged_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertTrue( $existing_section->open );
		$this->assertEquals( 'completed', $existing_section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $existing_section->date_completed );

		// Verify new sections from updated plan are present with default state
		$all_sections = $merged_plan->get_track( 'test_track_a' )->sections;
		$this->assertGreaterThan( 1, count( $all_sections ) );

		// Find a section that wasn't in the saved plan
		$new_section_found = false;
		foreach ( $all_sections as $section ) {
			if ( 'test_section_1' !== $section->id ) {
				$this->assertTrue( $section->open ); // Default state
				$this->assertEquals( 'new', $section->status ); // Default state
				$this->assertNull( $section->date_completed ); // Default state
				$new_section_found = true;
				break;
			}
		}
		$this->assertTrue( $new_section_found, 'New sections should be present with default state' );
	}

	/**
	 * Test merge with new tracks added in updated plan
	 */
	public function test_merge_with_new_tracks() {
		// Create a new plan (current version)
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with only one track
		$saved_plan = TestPlanFactory::create_plan_with_track_modifications(
			'test_track_a',
			array(
				'open' => true, // User has opened this track
			)
		);

		// Remove the second track to simulate an old plan structure
		$saved_plan_data = $saved_plan->to_array();
		unset( $saved_plan_data['tracks'][1] );
		$saved_plan = new Plan( $saved_plan_data );

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify existing track state was preserved
		$existing_track = $merged_plan->get_track( 'test_track_a' );
		$this->assertTrue( $existing_track->open );

		// Verify new tracks from updated plan are present with default state
		$all_tracks = $merged_plan->tracks;
		$this->assertGreaterThan( 1, count( $all_tracks ) );

		// Find a track that wasn't in the saved plan
		$new_track_found = false;
		foreach ( $all_tracks as $track ) {
			if ( 'test_track_a' !== $track->id ) {
				$this->assertFalse( $track->open ); // Default state is false
				$new_track_found = true;
				break;
			}
		}
		$this->assertTrue( $new_track_found, 'New tracks should be present with default state' );
	}

	/**
	 * Test merge preserves all user progress across multiple levels
	 */
	public function test_merge_preserves_all_user_progress() {
		// Create a new plan using TestPlan
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with extensive user progress using TestPlanFactory
		$saved_plan = TestPlanFactory::create_custom_plan(
			array(
				'tracks' => array(
					array(
						'id'       => 'test_track_a',
						'open'     => false, // User closed this track
						'sections' => array(
							array(
								'id'             => 'test_section_1',
								'open'           => true,
								'status'         => 'completed',
								'date_completed' => '2024-01-01 12:00:00',
								'tasks'          => array(
									array(
										'id'     => 'test_task_1',
										'status' => 'done',
									),
									array(
										'id'     => 'test_task_2',
										'status' => 'dismissed',
									),
								),
							),
							array(
								'id'             => 'test_section_2',
								'open'           => false,
								'status'         => 'in_progress',
								'date_completed' => null,
								'tasks'          => array(
									array(
										'id'     => 'test_task_3',
										'status' => 'new',
									),
								),
							),
						),
					),
				),
			)
		);

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify track state was preserved
		$merged_track = $merged_plan->get_track( 'test_track_a' );
		$this->assertNotNull( $merged_track );
		$this->assertFalse( $merged_track->open );

		// Verify first section state was preserved
		$merged_section1 = $merged_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertNotNull( $merged_section1 );
		$this->assertTrue( $merged_section1->open );
		$this->assertEquals( 'completed', $merged_section1->status );
		$this->assertEquals( '2024-01-01 12:00:00', $merged_section1->date_completed );

		// Verify second section state was preserved (if it exists)
		$merged_section2 = $merged_plan->get_section( 'test_track_a', 'test_section_2' );
		if ( $merged_section2 ) {
			$this->assertFalse( $merged_section2->open );
			$this->assertEquals( 'in_progress', $merged_section2->status );
			$this->assertNull( $merged_section2->date_completed );
		}

		// Verify all task statuses were preserved
		$merged_task1 = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertNotNull( $merged_task1 );
		$this->assertEquals( 'done', $merged_task1->status );

		$merged_task2 = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_2' );
		if ( $merged_task2 ) {
			$this->assertEquals( 'dismissed', $merged_task2->status );
		}

		$merged_task3 = $merged_plan->get_task( 'test_track_a', 'test_section_2', 'test_task_3' );
		if ( $merged_task3 ) {
			$this->assertEquals( 'new', $merged_task3->status );
		}
	}

	/**
	 * Test merge with language change scenario
	 */
	public function test_merge_with_language_change() {
		// Create a new plan (with updated language content) using TestPlan
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a saved plan with user progress using TestPlanFactory
		$saved_plan = TestPlanFactory::create_custom_plan(
			array(
				'tracks' => array(
					array(
						'id'       => 'test_track_a',
						'open'     => true,
						'sections' => array(
							array(
								'id'             => 'test_section_1',
								'open'           => true,
								'status'         => 'completed',
								'date_completed' => '2024-01-01 12:00:00',
								'tasks'          => array(
									array(
										'id'     => 'test_task_1',
										'status' => 'done',
									),
								),
							),
						),
					),
				),
			)
		);

		// Merge the plans (simulating language change)
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify user progress was preserved
		$merged_track = $merged_plan->get_track( 'test_track_a' );
		$this->assertNotNull( $merged_track );
		$this->assertTrue( $merged_track->open );

		$merged_section = $merged_plan->get_section( 'test_track_a', 'test_section_1' );
		$this->assertNotNull( $merged_section );
		$this->assertTrue( $merged_section->open );
		$this->assertEquals( 'completed', $merged_section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $merged_section->date_completed );

		$merged_task = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertNotNull( $merged_task );
		$this->assertEquals( 'done', $merged_task->status );

		// Verify that the plan structure is complete (new language content merged in)
		$this->assertGreaterThan( 0, count( $merged_plan->tracks ) );
		$this->assertGreaterThan( 0, count( $merged_track->sections ) );
		$this->assertGreaterThan( 0, count( $merged_section->tasks ) );
	}

	/**
	 * Test merge with empty saved plan
	 */
	public function test_merge_with_empty_saved_plan() {
		// Create a new plan using TestPlan
		$new_plan = TestPlanFactory::create_test_plan();

		// Create an empty saved plan using TestPlanFactory
		$saved_plan = TestPlanFactory::create_custom_plan(
			array(
				'tracks' => array(),
			)
		);

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify the new plan structure is used
		$this->assertEquals( 'test_plan', $merged_plan->id );
		$this->assertEquals( 'custom', $merged_plan->type );
		$this->assertGreaterThan( 0, count( $merged_plan->tracks ) );

		// Verify all tracks have default state (TestPlan tracks are closed by default)
		foreach ( $merged_plan->tracks as $track ) {
			$this->assertFalse( $track->open );
		}
	}

	/**
	 * Test merge with corrupted saved plan data
	 */
	public function test_merge_with_corrupted_saved_plan() {
		// Create a new plan using TestPlan
		$new_plan = TestPlanFactory::create_test_plan();

		// Create a corrupted saved plan (missing required fields) using TestPlanFactory
		$saved_plan = TestPlanFactory::create_custom_plan(
			array(
				'tracks' => array(
					array(
						'id'       => 'test_track_a',
						'sections' => array(
							array(
								'id'    => 'test_section_1',
								'tasks' => array(
									array(
										'id'     => 'test_task_1',
										'status' => 'done',
									),
								),
							),
						),
					),
				),
			)
		);

		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );

		// Verify the merge still works and preserves what it can
		$this->assertEquals( 'test_plan', $merged_plan->id );
		$this->assertEquals( 'custom', $merged_plan->type );

		// Verify the task status was preserved
		$merged_task = $merged_plan->get_task( 'test_track_a', 'test_section_1', 'test_task_1' );
		$this->assertNotNull( $merged_task );
		$this->assertEquals( 'done', $merged_task->status );
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
