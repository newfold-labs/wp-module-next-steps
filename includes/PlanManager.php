<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Track;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;
use NewfoldLabs\WP\Module\NextSteps\Data\Plans\StorePlan;
use NewfoldLabs\WP\Module\NextSteps\Data\Plans\BlogPlan;
use NewfoldLabs\WP\Module\NextSteps\Data\Plans\CorporatePlan;

/**
 * Plan Manager
 *
 * Handles plan loading, switching, and management based on nfd_solution option
 */
class PlanManager {

	/**
	 * Option name where the current plan is stored
	 */
	const OPTION = 'nfd_next_steps';

	/**
	 * Current version of plan data structure
	 * Increment this when plan data changes to trigger merges
	 */
	const PLAN_DATA_VERSION = NFD_NEXTSTEPS_MODULE_VERSION;

	/**
	 * Available plan types, this maps the site_type from onboarding module to internal plan types
	 *
	 * Maps nfd_module_onboarding_site_info['site_type'] values to internal plan types:
	 * - 'personal' (onboarding) -> 'blog' (internal plan)
	 * - 'business' (onboarding) -> 'corporate' (internal plan)
	 * - 'ecommerce' (onboarding) -> 'ecommerce' (internal plan)
	 */
	const PLAN_TYPES = array(
		'personal'  => 'blog',
		'business'  => 'corporate',
		'ecommerce' => 'ecommerce',
	);

	/**
	 * Get the current plan
	 *
	 * @return Plan|null
	 */
	public static function get_current_plan(): ?Plan {
		$plan_data = get_option( self::OPTION, array() );
		// $plan_data = array(); // uncomment to reset plan data for debugging
		if ( empty( $plan_data ) ) {
			// Load default plan based on solution
			return PlanLoader::load_default_plan();
		}

		// Convert array data to Plan object immediately
		$saved_plan = Plan::from_array( $plan_data );

		// Check if we need to merge with new plan data
		$saved_version   = $saved_plan->version ?: '1.0.0';
		$current_version = self::PLAN_DATA_VERSION;

		if ( version_compare( $saved_version, $current_version, '<' ) ) {
			// Version is outdated, need to merge with latest plan data

			// Load the appropriate new plan based on the saved plan type
			$new_plan = self::get_plan_type_data( $saved_plan->type );

			// Merge the saved data with the new plan (version will be updated automatically)
			$merged_plan = self::merge_plan_data( $saved_plan, $new_plan );

			// Save the merged plan with updated version
			self::save_plan( $merged_plan );

			return $merged_plan;
		}

		return $saved_plan;
	}

	/**
	 * Load plan type data
	 *
	 * @param string $plan_type Plan type
	 * @return Plan
	 */
	public static function get_plan_type_data( string $plan_type ): Plan {
		$plan = null;
		switch ( $plan_type ) {
			case 'blog':
				if ( ! class_exists( 'NewfoldLabs\WP\Module\NextSteps\Data\Plans\BlogPlan' ) ) {
					require_once __DIR__ . '/includes/Data/Plans/BlogPlan.php';
				}
				$plan = BlogPlan::get_plan();
				break;
			case 'corporate':
				if ( ! class_exists( 'NewfoldLabs\WP\Module\NextSteps\Data\Plans\CorporatePlan' ) ) {
					require_once __DIR__ . '/includes/Data/Plans/CorporatePlan.php';
				}
				$plan = CorporatePlan::get_plan();
				break;
			case 'ecommerce':
				if ( ! class_exists( 'NewfoldLabs\WP\Module\NextSteps\Data\Plans\StorePlan' ) ) {
					require_once __DIR__ . '/includes/Data/Plans/StorePlan.php';
				}
				$plan = StorePlan::get_plan();
				break;
			default:
				// If no matching plan type, fall back to loading default
				$plan = PlanLoader::load_default_plan();
		}
		
		return $plan;
	}

	/**
	 * Save the current plan
	 *
	 * @param Plan $plan Plan to save
	 * @return bool
	 */
	public static function save_plan( Plan $plan ): bool {
		$plan_data = $plan->to_array();
		return update_option( self::OPTION, $plan_data );
	}

	/**
	 * Merge existing saved plan data with new plan data from code
	 * Uses DTO merge methods for clean object-oriented approach
	 *
	 * @param Plan $saved_plan Existing saved plan data
	 * @param Plan $new_plan   New plan data from code
	 * @return Plan Merged plan
	 */
	public static function merge_plan_data( Plan $saved_plan, Plan $new_plan ): Plan {
		// Use the Plan DTO's merge method for clean object-oriented merging
		return $new_plan->merge_with( $saved_plan );
	}


	/**
	 * Switch to a different plan type
	 *
	 * @param string $plan_type Plan type to switch to
	 * @return Plan|false
	 */
	public static function switch_plan( string $plan_type ) {
		if (
			! in_array( $plan_type, array_values( self::PLAN_TYPES ), true ) &&
			! in_array( $plan_type, array_keys( self::PLAN_TYPES ), true )
		) {
			return false;
		}

		// If we received an onboarding site_type, convert it to internal plan type
		if ( array_key_exists( $plan_type, self::PLAN_TYPES ) ) {
			$plan_type = self::PLAN_TYPES[ $plan_type ];
		}

		// Load the appropriate plan directly
		$plan = self::get_plan_type_data( $plan_type );

		// Save the loaded plan
		self::save_plan( $plan );

		return $plan;
	}

	/**
	 * Update task status
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $task_id Task ID
	 * @param string $status New status
	 * @return bool
	 */
	public static function update_task_status( string $track_id, string $section_id, string $task_id, string $status ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$success = $plan->update_task_status( $track_id, $section_id, $task_id, $status );
		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}

	/**
	 * Get task by IDs
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $task_id Task ID
	 * @return Task|null
	 */
	public static function get_task( string $track_id, string $section_id, string $task_id ): ?Task {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return null;
		}

		return $plan->get_task( $track_id, $section_id, $task_id );
	}

	/**
	 * Add task to a section
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param Task   $task Task to add
	 * @return bool
	 */
	public static function add_task( string $track_id, string $section_id, Task $task ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$section = $plan->get_section( $track_id, $section_id );
		if ( ! $section ) {
			return false;
		}

		$success = $section->add_task( $task );
		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}

	/**
	 * Reset plan to defaults
	 *
	 * @return Plan
	 */
	public static function reset_plan(): Plan {
		delete_option( self::OPTION );
		return PlanLoader::load_default_plan();
	}

	/**
	 * Get plan statistics
	 *
	 * @return array
	 */
	public static function get_plan_stats(): array {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return array();
		}

		return array(
			'completion_percentage' => $plan->get_completion_percentage(),
			'total_tasks'           => $plan->get_total_tasks_count(),
			'completed_tasks'       => $plan->get_completed_tasks_count(),
			'total_sections'        => $plan->get_total_sections_count(),
			'completed_sections'    => $plan->get_completed_sections_count(),
			'total_tracks'          => $plan->get_total_tracks_count(),
			'completed_tracks'      => $plan->get_completed_tracks_count(),
			'is_completed'          => $plan->is_completed(),
		);
	}

	/**
	 * Update section state (unified for both open and status)
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $type Type of update ('open' or 'status')
	 * @param mixed  $value Value to set (bool for 'open', string for 'status')
	 * @return bool
	 */
	public static function update_section_state( string $track_id, string $section_id, string $type, $value ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$success = false;
		if ( 'open' === $type ) {
			$success = $plan->update_section_open_state( $track_id, $section_id, (bool) $value );
		} elseif ( 'status' === $type ) {
			$success = $plan->update_status_for_section( $track_id, $section_id, (string) $value );
		}

		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}

	/**
	 * Update track open state
	 *
	 * @param string $track_id Track ID
	 * @param bool   $open Open state
	 * @return bool
	 */
	public static function update_track_status( string $track_id, bool $open ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$success = $plan->update_track_open_state( $track_id, $open );
		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}
}
