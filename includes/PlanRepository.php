<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Track;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;

/**
 * PlanRepository
 *
 * Handles plan persistence, data management, and CRUD operations.
 * Responsible for storing, retrieving, and managing plan data.
 */
class PlanRepository {

	/**
	 * Option name where the current plan is stored
	 */
	const OPTION = 'nfd_next_steps';

	/**
	 * Static cache for the current plan
	 *
	 * @var Plan|null
	 */
	private static $cached_plan = null;

	/**
	 * Flag to track if the cache is valid
	 *
	 * @var bool
	 */
	private static $cache_valid = false;

	/**
	 * Cache key to track plan data version for invalidation
	 *
	 * @var string|null
	 */
	private static $cache_key = null;

	/**
	 * Invalidate the static cache
	 *
	 * @return void
	 */
	public static function invalidate_cache(): void {
		self::$cached_plan = null;
		self::$cache_valid = false;
		self::$cache_key = null;
	}

	/**
	 * Generate a cache key based on the current plan data
	 *
	 * @param array $plan_data The plan data array
	 * @return string The cache key
	 */
	private static function generate_cache_key( array $plan_data ): string {
		return md5( serialize( $plan_data ) );
	}

	/**
	 * Get the current plan
	 *
	 * @return Plan|null
	 */
	public static function get_current_plan(): ?Plan {
		$plan_data = get_option( self::OPTION, array() );
		// $plan_data = array(); // uncomment to reset plan data for debugging
		
		// Generate cache key for current data
		$current_cache_key = self::generate_cache_key( $plan_data );
		
		// Check if we have a valid cached plan
		if (
			self::$cache_valid &&
			self::$cached_plan !== null &&
			self::$cache_key === $current_cache_key
		) {
			return self::$cached_plan;
		}

		$plan = null;
		
		if ( empty( $plan_data ) ) {
			// Load default plan based on site type
			$site_type    = PlanFactory::determine_site_type();
			$default_plan = PlanFactory::create_plan( $site_type );
			if ( $default_plan ) {
				// Save the default plan for future use
				self::save_plan( $default_plan );
				$plan = $default_plan;
			}
		} else {
			// Convert array data to Plan object
			$saved_plan = Plan::from_array( $plan_data );

			// Check if we need to merge with new plan data
			if ( $saved_plan->is_version_outdated() ) {
				// Version is outdated, need to merge with latest plan data

				// Load the appropriate new plan based on the saved plan type
				if ( 'custom' === $saved_plan->type ) {
					// For custom plans, create a new plan with the same structure
					$new_plan = PlanFactory::create_plan( $saved_plan->type, $saved_plan->to_array() );
				} else {
					$new_plan = PlanFactory::create_plan( $saved_plan->type );
				}

				// Merge the saved data with the new plan (version will be updated automatically)
				$merged_plan = $new_plan->merge_with( $saved_plan );

				// Save the merged plan with updated version
				self::save_plan( $merged_plan );

				$plan = $merged_plan;
			} else {
				$plan = $saved_plan;
			}
		}

		// Cache the result
		if ( $plan !== null ) {
			self::$cached_plan = $plan;
			self::$cache_valid = true;
			self::$cache_key = $current_cache_key;
		}

		return $plan;
	}

	/**
	 * Save the current plan
	 *
	 * @param Plan $plan Plan to save
	 * @return bool Whether the plan was saved
	 */
	public static function save_plan( Plan $plan ): bool {
		$plan_data = $plan->to_array();
		$result = update_option( self::OPTION, $plan_data );
		
		// Update cache with the saved plan after successful save
		if ( $result ) {
			self::$cached_plan = $plan;
			self::$cache_valid = true;
			self::$cache_key = self::generate_cache_key( $plan_data );
		}
		
		return $result;
	}


	/**
	 * Switch to a different plan type
	 *
	 * @param string $plan_type Plan type to switch to
	 * @return Plan|false
	 */
	public static function switch_plan( string $plan_type ) {
		if (
			! in_array( $plan_type, array_values( PlanFactory::PLAN_TYPES ), true ) &&
			! in_array( $plan_type, array_keys( PlanFactory::PLAN_TYPES ), true )
		) {
			return false;
		}

		// If we received an onboarding site_type, convert it to internal plan type
		if ( array_key_exists( $plan_type, PlanFactory::PLAN_TYPES ) ) {
			$plan_type = PlanFactory::PLAN_TYPES[ $plan_type ];
		}

		// Load the appropriate plan directly
		$plan = PlanFactory::create_plan( $plan_type );

		// Save the loaded plan (this will automatically update cache)
		self::save_plan( $plan );

		return $plan;
	}

	/**
	 * Update task status
	 *
	 * @param string $track_id   Track ID
	 * @param string $section_id Section ID
	 * @param string $task_id    Task ID
	 * @param string $status     New status
	 * @return bool
	 */
	public static function update_task_status( string $track_id, string $section_id, string $task_id, string $status ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$updated = $plan->update_task_status( $track_id, $section_id, $task_id, $status );
		if ( $updated ) {
			return self::save_plan( $plan );
		}

		return false;
	}

	/**
	 * Get a specific task
	 *
	 * @param string $track_id   Track ID
	 * @param string $section_id Section ID
	 * @param string $task_id    Task ID
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
	 * Add a task to a section
	 *
	 * @param string $track_id   Track ID
	 * @param string $section_id Section ID
	 * @param Task   $task       Task to add
	 * @return bool
	 */
	public static function add_task( string $track_id, string $section_id, Task $task ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$added = $plan->add_task( $track_id, $section_id, $task );
		if ( $added ) {
			return self::save_plan( $plan );
		}

		return false;
	}

	/**
	 * Reset plan to default
	 *
	 * @return Plan
	 */
	public static function reset_plan(): Plan {
		$default_plan = PlanFactory::load_default_plan();
		// save_plan will automatically update cache
		self::save_plan( $default_plan );
		return $default_plan;
	}

	/**
	 * Get plan statistics
	 *
	 * @return array The plan statistics
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
		);
	}

	/**
	 * Update section state
	 *
	 * @param string $track_id   Track ID
	 * @param string $section_id Section ID
	 * @param string $type       Type of update ('open' or 'status')
	 * @param mixed  $value      New value
	 * @return bool Whether the section state was updated
	 */
	public static function update_section_state( string $track_id, string $section_id, string $type, $value ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$updated = false;
		if ( 'open' === $type ) {
			$updated = $plan->update_section_open( $track_id, $section_id, boolval( $value ) );
		} elseif ( 'status' === $type ) {
			$updated = $plan->update_section_status( $track_id, $section_id, $value );
		}

		if ( $updated ) {
			return self::save_plan( $plan );
		}

		return false;
	}

	/**
	 * Update track status
	 *
	 * @param string $track_id Track ID
	 * @param bool   $open     Whether track should be open/expanded
	 * @return bool Whether the track status was updated
	 */
	public static function update_track_status( string $track_id, bool $open ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$updated = $plan->update_track_open_state( $track_id, $open );
		if ( $updated ) {
			return self::save_plan( $plan );
		}

		return false;
	}
}
