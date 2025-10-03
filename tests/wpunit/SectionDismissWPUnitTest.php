<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;

/**
 * WordPress Unit Tests for Section Dismiss Functionality
 *
 * Tests the mark_all_active_tasks_dismissed() method and the
 * automatic task dismissal when a section is dismissed.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\DTOs\Section
 */
class SectionDismissWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Helper method to create a test section with tasks
	 *
	 * @param int $task_count Number of tasks to create.
	 * @return Section
	 */
	private function create_test_section( $task_count = 3 ) {
		$tasks = array();
		for ( $i = 1; $i <= $task_count; $i++ ) {
			$tasks[] = array(
				'id'       => "task_{$i}",
				'title'    => "Test Task {$i}",
				'status'   => 'new',
				'priority' => $i,
			);
		}

		return new Section(
			array(
				'id'    => 'test_section',
				'label' => 'Test Section',
				'tasks' => $tasks,
			)
		);
	}

	/**
	 * Test mark_all_active_tasks_dismissed dismisses all new tasks
	 */
	public function test_mark_all_active_tasks_dismissed_dismisses_new_tasks() {
		$section = $this->create_test_section( 3 );

		// All tasks should be new
		$this->assertEquals( 'new', $section->tasks[0]->status );
		$this->assertEquals( 'new', $section->tasks[1]->status );
		$this->assertEquals( 'new', $section->tasks[2]->status );

		// Mark all as dismissed
		$updated_count = $section->mark_all_active_tasks_dismissed();

		// Should return count of updated tasks
		$this->assertEquals( 3, $updated_count );

		// All tasks should now be dismissed
		$this->assertEquals( 'dismissed', $section->tasks[0]->status );
		$this->assertEquals( 'dismissed', $section->tasks[1]->status );
		$this->assertEquals( 'dismissed', $section->tasks[2]->status );
	}

	/**
	 * Test mark_all_active_tasks_dismissed skips already dismissed tasks
	 */
	public function test_mark_all_active_tasks_dismissed_skips_already_dismissed() {
		$section = $this->create_test_section( 3 );

		// Manually dismiss one task
		$section->tasks[1]->update_status( 'dismissed' );

		// Mark all as dismissed
		$updated_count = $section->mark_all_active_tasks_dismissed();

		// Should only update 2 tasks (not the already dismissed one)
		$this->assertEquals( 2, $updated_count );

		// All should be dismissed
		$this->assertEquals( 'dismissed', $section->tasks[0]->status );
		$this->assertEquals( 'dismissed', $section->tasks[1]->status );
		$this->assertEquals( 'dismissed', $section->tasks[2]->status );
	}

	/**
	 * Test mark_all_active_tasks_dismissed with mixed task statuses
	 */
	public function test_mark_all_active_tasks_dismissed_with_mixed_statuses() {
		$section = $this->create_test_section( 5 );

		// Set mixed statuses
		$section->tasks[0]->update_status( 'new' );      // Should be dismissed
		$section->tasks[1]->update_status( 'dismissed' ); // Already dismissed
		$section->tasks[2]->update_status( 'done' );      // Already done - should be dismissed per method logic
		$section->tasks[3]->update_status( 'new' );      // Should be dismissed
		$section->tasks[4]->update_status( 'dismissed' ); // Already dismissed

		// Mark all as dismissed
		$updated_count = $section->mark_all_active_tasks_dismissed();

		// Should update 3 tasks: 2 'new' tasks + 1 'done' task (done tasks are not dismissed, so they get updated)
		$this->assertEquals( 3, $updated_count );

		// Check final statuses
		$this->assertEquals( 'dismissed', $section->tasks[0]->status );
		$this->assertEquals( 'dismissed', $section->tasks[1]->status );
		$this->assertEquals( 'dismissed', $section->tasks[2]->status ); // Will be dismissed since not already dismissed
		$this->assertEquals( 'dismissed', $section->tasks[3]->status );
		$this->assertEquals( 'dismissed', $section->tasks[4]->status );
	}

	/**
	 * Test mark_all_active_tasks_dismissed returns zero when all tasks already dismissed
	 */
	public function test_mark_all_active_tasks_dismissed_returns_zero_when_all_dismissed() {
		$section = $this->create_test_section( 3 );

		// Dismiss all tasks first
		foreach ( $section->tasks as $task ) {
			$task->update_status( 'dismissed' );
		}

		// Try to dismiss again
		$updated_count = $section->mark_all_active_tasks_dismissed();

		// Should return 0 since nothing was updated
		$this->assertEquals( 0, $updated_count );
	}

	/**
	 * Test mark_all_active_tasks_dismissed with empty tasks array
	 */
	public function test_mark_all_active_tasks_dismissed_with_no_tasks() {
		$section = new Section(
			array(
				'id'    => 'empty_section',
				'label' => 'Empty Section',
				'tasks' => array(),
			)
		);

		$updated_count = $section->mark_all_active_tasks_dismissed();

		$this->assertEquals( 0, $updated_count );
	}

	/**
	 * Test update_status to dismissed automatically dismisses tasks
	 */
	public function test_update_status_dismissed_auto_dismisses_tasks() {
		$section = $this->create_test_section( 3 );

		// All tasks should be new
		$this->assertEquals( 'new', $section->tasks[0]->status );
		$this->assertEquals( 'new', $section->tasks[1]->status );
		$this->assertEquals( 'new', $section->tasks[2]->status );

		// Dismiss the section
		$result = $section->update_status( 'dismissed' );

		$this->assertTrue( $result );
		$this->assertEquals( 'dismissed', $section->status );

		// All tasks should now be dismissed automatically
		$this->assertEquals( 'dismissed', $section->tasks[0]->status );
		$this->assertEquals( 'dismissed', $section->tasks[1]->status );
		$this->assertEquals( 'dismissed', $section->tasks[2]->status );
	}

	/**
	 * Test update_status to done keeps tasks marked as done (not dismissed)
	 */
	public function test_update_status_done_marks_tasks_done_not_dismissed() {
		$section = $this->create_test_section( 3 );

		// Mark section as done
		$result = $section->update_status( 'done' );

		$this->assertTrue( $result );
		$this->assertEquals( 'done', $section->status );

		// Tasks should be marked as done, not dismissed
		$this->assertEquals( 'done', $section->tasks[0]->status );
		$this->assertEquals( 'done', $section->tasks[1]->status );
		$this->assertEquals( 'done', $section->tasks[2]->status );
	}

	/**
	 * Test section dismiss sets dismissed status
	 */
	public function test_section_dismiss_sets_dismissed_status() {
		$section = $this->create_test_section( 2 );

		// Initially new
		$this->assertEquals( 'new', $section->status );

		$section->update_status( 'dismissed' );

		// Should now be dismissed
		$this->assertEquals( 'dismissed', $section->status );
	}

	/**
	 * Test dismissed tasks also get dismissed status
	 */
	public function test_dismissed_tasks_get_dismissed_status() {
		$section = $this->create_test_section( 2 );

		$this->assertFalse( $section->tasks[0]->is_dismissed() );
		$this->assertFalse( $section->tasks[1]->is_dismissed() );

		$section->update_status( 'dismissed' );

		$this->assertTrue( $section->tasks[0]->is_dismissed() );
		$this->assertTrue( $section->tasks[1]->is_dismissed() );
	}

	/**
	 * Test mark_all_active_tasks_dismissed with section having done tasks
	 */
	public function test_mark_all_active_tasks_dismissed_with_done_tasks() {
		$section = $this->create_test_section( 4 );

		// Mark some tasks as done
		$section->tasks[1]->update_status( 'done' );
		$section->tasks[3]->update_status( 'done' );

		// Dismiss section
		$section->update_status( 'dismissed' );

		// Note: Current implementation dismisses ALL non-dismissed tasks, including done tasks
		// This is the actual behavior - done tasks get dismissed when section is dismissed
		$this->assertEquals( 'dismissed', $section->tasks[1]->status );
		$this->assertEquals( 'dismissed', $section->tasks[3]->status );
		$this->assertTrue( $section->tasks[1]->is_dismissed() );
		$this->assertTrue( $section->tasks[3]->is_dismissed() );

		// New tasks should also be dismissed
		$this->assertEquals( 'dismissed', $section->tasks[0]->status );
		$this->assertEquals( 'dismissed', $section->tasks[2]->status );
		$this->assertTrue( $section->tasks[0]->is_dismissed() );
		$this->assertTrue( $section->tasks[2]->is_dismissed() );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		parent::tearDown();
	}
}

