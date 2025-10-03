<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\TaskStateValidator;

/**
 * WordPress Unit Tests for TaskStateValidator
 *
 * Tests the TaskStateValidator registry and validation system.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\TaskStateValidator
 */
class TaskStateValidatorWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );

		// Invalidate static cache
		PlanRepository::invalidate_cache();

		// Reset the validator registry between tests
		$reflection = new \ReflectionClass( TaskStateValidator::class );
		$property   = $reflection->getProperty( 'validators' );
		$property->setAccessible( true );
		$property->setValue( array() );
	}

	/**
	 * Test register_validator stores validator in registry
	 */
	public function test_register_validator_stores_validator() {
		$task_path = 'blog_setup.blog_build_track.create_content.blog_first_post';
		$callback  = function () {
			return true;
		};

		TaskStateValidator::register_validator( $task_path, $callback );

		// Get the validators using reflection
		$reflection = new \ReflectionClass( TaskStateValidator::class );
		$property   = $reflection->getProperty( 'validators' );
		$property->setAccessible( true );
		$validators = $property->getValue();

		$this->assertArrayHasKey( $task_path, $validators );
		$this->assertEquals( $callback, $validators[ $task_path ] );
	}

	/**
	 * Test register_validator allows multiple validators
	 */
	public function test_register_validator_allows_multiple_validators() {
		$task_path_1 = 'blog_setup.blog_build_track.create_content.blog_first_post';
		$task_path_2 = 'store_setup.store_build_track.setup_products.store_add_product';

		TaskStateValidator::register_validator( $task_path_1, function () {
			return true;
		} );
		TaskStateValidator::register_validator( $task_path_2, function () {
			return false;
		} );

		// Get the validators
		$reflection = new \ReflectionClass( TaskStateValidator::class );
		$property   = $reflection->getProperty( 'validators' );
		$property->setAccessible( true );
		$validators = $property->getValue();

		$this->assertCount( 2, $validators );
		$this->assertArrayHasKey( $task_path_1, $validators );
		$this->assertArrayHasKey( $task_path_2, $validators );
	}

	/**
	 * Test validate_existing_state marks task complete when validator returns true
	 */
	public function test_validate_existing_state_marks_task_complete() {
		$plan = PlanFactory::create_plan( 'blog' );

		// Register a validator that returns true
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.create_content.blog_first_post',
			function () {
				return true; // Condition met
			}
		);

		// Verify task is not complete initially
		$task = $plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertEquals( 'new', $task->status );

		// Run validation
		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		// Task should now be marked complete
		$task = $plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertEquals( 'done', $task->status );
		$this->assertContains( 'blog_setup.blog_build_track.create_content.blog_first_post', $completed_tasks );
	}

	/**
	 * Test validate_existing_state does not mark task complete when validator returns false
	 */
	public function test_validate_existing_state_does_not_mark_task_when_validator_false() {
		$plan = PlanFactory::create_plan( 'blog' );

		// Register a validator that returns false
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.create_content.blog_first_post',
			function () {
				return false; // Condition not met
			}
		);

		// Verify task is new
		$task = $plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertEquals( 'new', $task->status );

		// Run validation
		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		// Task should still be new
		$task = $plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertEquals( 'new', $task->status );
		$this->assertEmpty( $completed_tasks );
	}

	/**
	 * Test validate_existing_state skips tasks that are already complete
	 */
	public function test_validate_existing_state_skips_already_complete_tasks() {
		$plan = PlanFactory::create_plan( 'blog' );

		// Mark task as complete first
		$plan->update_task_status( 'blog_build_track', 'create_content', 'blog_first_post', 'done' );

		// Register a validator (should not be called for already complete tasks)
		$validator_called = false;
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.create_content.blog_first_post',
			function () use ( &$validator_called ) {
				$validator_called = true;
				return true;
			}
		);

		// Run validation
		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		// Validator should not have been called
		$this->assertFalse( $validator_called );
		$this->assertEmpty( $completed_tasks );
	}

	/**
	 * Test validate_existing_state only validates tasks for the current plan
	 */
	public function test_validate_existing_state_only_validates_current_plan() {
		$plan = PlanFactory::create_plan( 'blog' );

		// Register validators for different plans
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.create_content.blog_first_post',
			function () {
				return true;
			}
		);
		TaskStateValidator::register_validator(
			'store_setup.store_build_track.setup_products.store_add_product',
			function () {
				return true;
			}
		);

		// Run validation on blog plan
		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		// Only blog task should be in results
		$this->assertCount( 1, $completed_tasks );
		$this->assertContains( 'blog_setup.blog_build_track.create_content.blog_first_post', $completed_tasks );
		$this->assertNotContains( 'store_setup.store_build_track.setup_products.store_add_product', $completed_tasks );
	}

	/**
	 * Test validate_existing_state handles multiple validators for same plan
	 */
	public function test_validate_existing_state_handles_multiple_validators() {
		$plan = PlanFactory::create_plan( 'blog' );

		// Register multiple validators for blog plan
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.create_content.blog_first_post',
			function () {
				return true;
			}
		);
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.customize_blog.blog_upload_logo',
			function () {
				return true;
			}
		);
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.basic_blog_setup.blog_quick_setup',
			function () {
				return false; // This one should not complete
			}
		);

		// Run validation
		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		// Should have 2 completed tasks
		$this->assertCount( 2, $completed_tasks );
		$this->assertContains( 'blog_setup.blog_build_track.create_content.blog_first_post', $completed_tasks );
		$this->assertContains( 'blog_setup.blog_build_track.customize_blog.blog_upload_logo', $completed_tasks );
	}

	/**
	 * Test validate_existing_state skips invalid task paths
	 */
	public function test_validate_existing_state_skips_invalid_paths() {
		$plan = PlanFactory::create_plan( 'blog' );

		// Register validators with invalid paths
		TaskStateValidator::register_validator(
			'invalid_path',
			function () {
				return true;
			}
		);
		TaskStateValidator::register_validator(
			'blog_setup.wrong_track.wrong_section.wrong_task',
			function () {
				return true;
			}
		);

		// Run validation
		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		// No tasks should be completed
		$this->assertEmpty( $completed_tasks );
	}

	/**
	 * Test validate_existing_state handles validator exceptions gracefully
	 */
	public function test_validate_existing_state_handles_exceptions() {
		$plan = PlanFactory::create_plan( 'blog' );

		// Register a validator that throws an exception
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.create_content.blog_first_post',
			function () {
				throw new \Exception( 'Test exception' );
			}
		);

		// Register another validator that works
		TaskStateValidator::register_validator(
			'blog_setup.blog_build_track.customize_blog.blog_upload_logo',
			function () {
				return true;
			}
		);

		// Run validation - should not fatal error
		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		// First task should not be complete, second should be
		$task1 = $plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$task2 = $plan->get_task( 'blog_build_track', 'customize_blog', 'blog_upload_logo' );

		$this->assertEquals( 'new', $task1->status );
		$this->assertEquals( 'done', $task2->status );
		$this->assertCount( 1, $completed_tasks );
	}

	/**
	 * Test validate_existing_state with store plan
	 */
	public function test_validate_existing_state_with_store_plan() {
		$plan = PlanFactory::create_plan( 'ecommerce' );

		TaskStateValidator::register_validator(
			'store_setup.store_build_track.setup_products.store_add_product',
			function () {
				return true;
			}
		);

		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		$this->assertCount( 1, $completed_tasks );
		$this->assertContains( 'store_setup.store_build_track.setup_products.store_add_product', $completed_tasks );

		$task = $plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test validate_existing_state returns empty array when no validators registered
	 */
	public function test_validate_existing_state_with_no_validators() {
		$plan = PlanFactory::create_plan( 'blog' );

		$completed_tasks = TaskStateValidator::validate_existing_state( $plan );

		$this->assertIsArray( $completed_tasks );
		$this->assertEmpty( $completed_tasks );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up
		delete_option( PlanRepository::OPTION );

		parent::tearDown();
	}
}

