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
		$plan_data = array();
		if ( empty( $plan_data ) ) {
			// Load default plan based on solution
			return PlanLoader::load_default_plan();
		}

		// Check if we need to merge with new plan data
		$saved_version   = $plan_data['version'] ?? '0.0.0';
		$current_version = self::PLAN_DATA_VERSION;

		if ( version_compare( $saved_version, $current_version, '<' ) ) {
			// Version is outdated, need to merge with latest plan data

			// First determine what plan type this is based on saved data
			$plan_id  = $plan_data['id'] ?? '';
			$new_plan = null;

			// Load the appropriate new plan based on the saved plan ID
			switch ( $plan_id ) {
				case 'blog':
					$new_plan = BlogPlan::get_plan();
					break;
				case 'corporate':
					$new_plan = CorporatePlan::get_plan();
					break;
				case 'ecommerce':
					$new_plan = StorePlan::get_plan();
					break;
				default:
					// If we can't determine the plan type, fall back to loading default
					return PlanLoader::load_default_plan();
			}

			// Merge the saved data with the new plan
			$merged_plan = self::merge_plan_data( $plan_data, $new_plan );

			// Save the merged plan with updated version
			self::save_plan( $merged_plan );

			return $merged_plan;
		}

		return Plan::from_array( $plan_data );
	}

	/**
	 * Save the current plan
	 *
	 * @param Plan $plan Plan to save
	 * @return bool
	 */
	public static function save_plan( Plan $plan ): bool {
		// Add version information to the saved data
		$plan_data            = $plan->to_array();
		$plan_data['version'] = self::PLAN_DATA_VERSION;
		return update_option( self::OPTION, $plan_data );
	}

	/**
	 * Merge existing saved plan data with new plan data from code
	 * Updates titles, descriptions, hrefs, priorities while preserving IDs and status
	 *
	 * @param array $saved_data Existing saved plan data
	 * @param Plan  $new_plan   New plan data from code
	 * @return Plan Merged plan
	 */
	public static function merge_plan_data( array $saved_data, Plan $new_plan ): Plan {
		// Create a map of existing tasks by ID for quick lookup
		$existing_tasks = array();
		if ( isset( $saved_data['tracks'] ) && is_array( $saved_data['tracks'] ) ) {
			foreach ( $saved_data['tracks'] as $track ) {
				if ( isset( $track['sections'] ) && is_array( $track['sections'] ) ) {
					foreach ( $track['sections'] as $section ) {
						if ( isset( $section['tasks'] ) && is_array( $section['tasks'] ) ) {
							foreach ( $section['tasks'] as $task ) {
								if ( isset( $task['id'] ) ) {
									$existing_tasks[ $task['id'] ] = $task;
								}
							}
						}
					}
				}
			}
		}

		// Create the merged plan by updating new plan with preserved status
		$merged_tracks = array();
		foreach ( $new_plan->get_tracks() as $track ) {
			$merged_sections = array();
			foreach ( $track->get_sections() as $section ) {
				$merged_tasks = array();
				foreach ( $section->get_tasks() as $task ) {
					$task_data = $task->to_array();

					// If this task exists in saved data, preserve its status and any custom data
					if ( isset( $existing_tasks[ $task->get_id() ] ) ) {
						$existing_task = $existing_tasks[ $task->get_id() ];

						// Preserve status (this is the key user state we want to keep)
						if ( isset( $existing_task['status'] ) ) {
							$task_data['status'] = $existing_task['status'];
						}

						// Preserve any custom completion date if it exists
						if ( isset( $existing_task['completed_at'] ) ) {
							$task_data['completed_at'] = $existing_task['completed_at'];
						}

						// Preserve any custom dismissal date if it exists
						if ( isset( $existing_task['dismissed_at'] ) ) {
							$task_data['dismissed_at'] = $existing_task['dismissed_at'];
						}

						// Preserve any other custom metadata that might have been added
						foreach ( $existing_task as $key => $value ) {
							if ( ! in_array( $key, array( 'id', 'title', 'description', 'href', 'priority', 'source' ), true ) ) {
								$task_data[ $key ] = $value;
							}
						}
					}
					$merged_tasks[] = new Task(
						$task_data['id'],
						$task_data['title'],
						$task_data['description'] ?? '',
						$task_data['href'] ?? '',
						$task_data['status'] ?? 'new',
						$task_data['priority'] ?? 1,
						$task_data['source'] ?? 'wp-module-next-steps',
						$task_data
					);
				}
				$merged_sections[] = new Section(
					$section->get_id(),
					$section->get_label(),
					$section->get_description(),
					$merged_tasks
				);
			}
			$merged_tracks[] = new Track(
				$track->get_id(),
				$track->get_label(),
				$track->get_description(),
				$merged_sections
			);
		}
		return new Plan(
			$new_plan->get_id(),
			$new_plan->get_label(),
			$new_plan->get_description(),
			$merged_tracks
		);
	}

	/**
	 * Switch to a different plan type
	 *
	 * @param string $plan_type Plan type to switch to
	 * @return Plan|false
	 */
	public static function switch_plan( string $plan_type ) {
		if ( ! in_array( $plan_type, array_values( self::PLAN_TYPES ), true ) && ! in_array( $plan_type, array_keys( self::PLAN_TYPES ), true ) ) {
			return false;
		}

		// If we received an onboarding site_type, convert it to internal plan type
		if ( array_key_exists( $plan_type, self::PLAN_TYPES ) ) {
			$plan_type = self::PLAN_TYPES[ $plan_type ];
		}

		// Clear current plan to force reload
		// delete_option( self::OPTION );

		// Load the appropriate plan directly
		switch ( $plan_type ) {
			case 'ecommerce':
				$plan = StorePlan::get_plan();
				break;
			case 'corporate':
				$plan = CorporatePlan::get_plan();
				break;
			case 'blog':
			default:
				$plan = BlogPlan::get_plan();
			break;
		}

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
	 * Update section open state
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param bool   $open Open state
	 * @return bool
	 */
	public static function update_section_status( string $track_id, string $section_id, bool $open ): bool {
		$plan = self::get_current_plan();
		if ( ! $plan ) {
			return false;
		}

		$success = $plan->update_section_open_state( $track_id, $section_id, $open );
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

	/**
	 * Update section status
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $status New status
	 * @return bool
	 */
	public static function update_status_for_section( string $track_id, string $section_id, string $status ): bool {
		$plan = self::get_current_plan();

		if ( ! $plan ) {
			return false;
		}

		$success = $plan->update_status_for_section( $track_id, $section_id, $status );
		if ( $success ) {
			self::save_plan( $plan );
		}

		return $success;
	}
}
