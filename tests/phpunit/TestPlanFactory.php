<?php

namespace NewfoldLabs\WP\Module\NextSteps\Tests\PHPUnit;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\PlanFactory;

/**
 * Test Plan Factory
 *
 * Factory class for creating Plan objects from test data.
 * This provides a clean interface for tests to create plans
 * without being coupled to the actual plan data structures.
 */
class TestPlanFactory {

	/**
	 * Create a complete test plan
	 *
	 * @return Plan Test plan object
	 */
	public static function create_test_plan(): Plan {
		return PlanFactory::create_plan( 'custom', TestPlan::get_plan_data() );
	}

	/**
	 * Create a minimal test plan
	 *
	 * @return Plan Minimal test plan object
	 */
	public static function create_minimal_plan(): Plan {
		return PlanFactory::create_plan( 'custom', TestPlan::get_minimal_plan_data() );
	}

	/**
	 * Create a test plan with user progress
	 *
	 * @return Plan Test plan with user progress
	 */
	public static function create_plan_with_progress(): Plan {
		return PlanFactory::create_plan( 'custom', TestPlan::get_plan_with_progress_data() );
	}

	/**
	 * Create a test plan with old version
	 *
	 * @return Plan Test plan with old version
	 */
	public static function create_old_version_plan(): Plan {
		return PlanFactory::create_plan( 'custom', TestPlan::get_old_version_plan_data() );
	}

	/**
	 * Create a test plan with custom modifications
	 *
	 * @param array $modifications Modifications to apply to the base plan
	 * @return Plan Modified test plan
	 */
	public static function create_custom_plan( array $modifications = array() ): Plan {
		$plan_data = TestPlan::get_plan_data();

		// Apply modifications recursively
		$plan_data = self::apply_modifications( $plan_data, $modifications );

		return PlanFactory::create_plan( 'custom', $plan_data );
	}

	/**
	 * Apply modifications to plan data recursively
	 *
	 * @param array $data Base data
	 * @param array $modifications Modifications to apply
	 * @return array Modified data
	 */
	private static function apply_modifications( array $data, array $modifications ): array {
		foreach ( $modifications as $key => $value ) {
			if ( is_array( $value ) && isset( $data[ $key ] ) && is_array( $data[ $key ] ) ) {
				$data[ $key ] = self::apply_modifications( $data[ $key ], $value );
			} else {
				$data[ $key ] = $value;
			}
		}
		return $data;
	}

	/**
	 * Create a test plan with specific track modifications
	 *
	 * @param string $track_id Track ID to modify
	 * @param array $track_modifications Track modifications
	 * @return Plan Modified test plan
	 */
	public static function create_plan_with_track_modifications( string $track_id, array $track_modifications ): Plan {
		$plan_data = TestPlan::get_plan_data();

		// Find and modify the specified track
		foreach ( $plan_data['tracks'] as &$track ) {
			if ( $track['id'] === $track_id ) {
				$track = array_merge( $track, $track_modifications );
				break;
			}
		}

		return PlanFactory::create_plan( 'custom', $plan_data );
	}

	/**
	 * Create a test plan with specific section modifications
	 *
	 * @param string $track_id Track ID containing the section
	 * @param string $section_id Section ID to modify
	 * @param array $section_modifications Section modifications
	 * @return Plan Modified test plan
	 */
	public static function create_plan_with_section_modifications( string $track_id, string $section_id, array $section_modifications ): Plan {
		$plan_data = TestPlan::get_plan_data();

		// Find and modify the specified section
		foreach ( $plan_data['tracks'] as &$track ) {
			if ( $track['id'] === $track_id ) {
				foreach ( $track['sections'] as &$section ) {
					if ( $section['id'] === $section_id ) {
						$section = array_merge( $section, $section_modifications );
						break 2;
					}
				}
			}
		}

		return PlanFactory::create_plan( 'custom', $plan_data );
	}

	/**
	 * Create a test plan with specific task modifications
	 *
	 * @param string $track_id Track ID containing the task
	 * @param string $section_id Section ID containing the task
	 * @param string $task_id Task ID to modify
	 * @param array $task_modifications Task modifications
	 * @return Plan Modified test plan
	 */
	public static function create_plan_with_task_modifications( string $track_id, string $section_id, string $task_id, array $task_modifications ): Plan {
		$plan_data = TestPlan::get_plan_data();

		// Find and modify the specified task
		foreach ( $plan_data['tracks'] as &$track ) {
			if ( $track['id'] === $track_id ) {
				foreach ( $track['sections'] as &$section ) {
					if ( $section['id'] === $section_id ) {
						foreach ( $section['tasks'] as &$task ) {
							if ( $task['id'] === $task_id ) {
								$task = array_merge( $task, $task_modifications );
								break 3;
							}
						}
					}
				}
			}
		}

		return PlanFactory::create_plan( 'custom', $plan_data );
	}
}
