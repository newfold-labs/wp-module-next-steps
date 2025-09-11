<?php

namespace NewfoldLabs\WP\Module\NextSteps\DTOs;

/**
 * Plan Data Transfer Object
 *
 * Represents a plan that contains multiple tracks
 */
class Plan {

	/**
	 * Plan identifier
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Plan type
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Plan label
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Plan description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Plan tracks
	 *
	 * @var Track[]
	 */
	public $tracks;

	/**
	 * Plan data version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Plan constructor
	 *
	 * @param array $data Plan data
	 */
	public function __construct( array $data = array() ) {
		$this->id          = $data['id'] ?? '';
		$this->type        = $data['type'] ?? '';
		$this->label       = $data['label'] ?? '';
		$this->description = $data['description'] ?? '';
		$this->version     = $data['version'] ?? ( defined( 'NFD_NEXTSTEPS_MODULE_VERSION' ) ? NFD_NEXTSTEPS_MODULE_VERSION : '1.0.0' );
		$this->tracks      = array();

		// Convert track arrays to Track objects
		if ( isset( $data['tracks'] ) && is_array( $data['tracks'] ) ) {
			foreach ( $data['tracks'] as $track_data ) {
				if ( $track_data instanceof Track ) {
					$this->tracks[] = $track_data;
				} else {
					$this->tracks[] = Track::from_array( $track_data );
				}
			}
		}
	}

	/**
	 * Convert Plan to array
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'id'          => $this->id,
			'type'        => $this->type,
			'label'       => $this->label,
			'description' => $this->description,
			'version'     => $this->version,
			'tracks'      => array_map(
				function ( Track $track ) {
					return $track->to_array();
				},
				$this->tracks
			),
		);
	}

	/**
	 * Merge this plan with saved plan data
	 * Preserves: id, type
	 * Updates: everything else
	 *
	 * @param Plan $saved_plan Saved plan data
	 * @return Plan Merged plan
	 */
	public function merge_with( Plan $saved_plan ): Plan {
		$merged_data = $this->to_array();
		// Preserve plan ID and type from saved data
		$merged_data['id']   = $saved_plan->id;
		$merged_data['type'] = $saved_plan->type;
		// Note: version is NOT preserved - it should be updated to current version

		// Merge tracks recursively
		$merged_tracks = array();
		foreach ( $this->tracks as $track ) {
			// Find matching saved track by ID
			$saved_track = null;
			foreach ( $saved_plan->tracks as $saved_track_candidate ) {
				if ( $saved_track_candidate->id === $track->id ) {
					$saved_track = $saved_track_candidate;
					break;
				}
			}
			if ( $saved_track ) {
				$merged_tracks[] = $track->merge_with( $saved_track );
			} else {
				$merged_tracks[] = $track;
			}
		}
		$merged_data['tracks'] = array_map(
			function ( Track $track ) {
				return $track->to_array();
			},
			$merged_tracks
		);
		return new Plan( $merged_data );
	}

	/**
	 * Create Plan from array
	 *
	 * @param array $data Plan data
	 * @return Plan
	 */
	public static function from_array( array $data ): Plan {
		return new self( $data );
	}


	/**
	 * Add track to plan
	 *
	 * @param Track $track Track to add
	 * @return bool
	 */
	public function add_track( Track $track ): bool {
		// Check if track with same ID already exists
		foreach ( $this->tracks as $existing_track ) {
			if ( $existing_track->id === $track->id ) {
				return false; // Track already exists
			}
		}

		$this->tracks[] = $track;
		return true;
	}

	/**
	 * Remove track from plan
	 *
	 * @param string $track_id Track ID to remove
	 * @return bool
	 */
	public function remove_track( string $track_id ): bool {
		foreach ( $this->tracks as $index => $track ) {
			if ( $track->id === $track_id ) {
				unset( $this->tracks[ $index ] );
				$this->tracks = array_values( $this->tracks ); // Reindex
				return true;
			}
		}
		return false;
	}

	/**
	 * Get track by ID
	 *
	 * @param string $track_id Track ID
	 * @return Track|null
	 */
	public function get_track( string $track_id ): ?Track {
		foreach ( $this->tracks as $track ) {
			if ( $track->id === $track_id ) {
				return $track;
			}
		}
		return null;
	}

	/**
	 * Get section by track and section ID
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @return Section|null
	 */
	public function get_section( string $track_id, string $section_id ): ?Section {
		$track = $this->get_track( $track_id );
		if ( $track ) {
			return $track->get_section( $section_id );
		}
		return null;
	}

	/**
	 * Get task by track, section, and task ID
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $task_id Task ID
	 * @return Task|null
	 */
	public function get_task( string $track_id, string $section_id, string $task_id ): ?Task {
		$track = $this->get_track( $track_id );
		if ( $track ) {
			return $track->get_task( $section_id, $task_id );
		}
		return null;
	}


	/**
	 * Get all sections from all tracks
	 *
	 * @return Section[]
	 */
	public function get_all_sections(): array {
		$sections = array();
		foreach ( $this->tracks as $track ) {
			$sections = array_merge( $sections, $track->sections );
		}
		return $sections;
	}

	/**
	 * Get all tasks from all tracks/sections
	 *
	 * @return Task[]
	 */
	public function get_all_tasks(): array {
		$tasks = array();
		foreach ( $this->tracks as $track ) {
			$tasks = array_merge( $tasks, $track->get_all_tasks() );
		}
		return $tasks;
	}

	/**
	 * Get plan completion percentage
	 *
	 * @return int
	 */
	public function get_completion_percentage(): int {
		if ( empty( $this->tracks ) ) {
			return 0;
		}

		$total_percentage = 0;
		foreach ( $this->tracks as $track ) {
			$total_percentage += $track->get_completion_percentage();
		}

		return intval( $total_percentage / count( $this->tracks ) );
	}

	/**
	 * Check if plan is completed
	 *
	 * @return bool
	 */
	public function is_completed(): bool {
		return $this->get_completion_percentage() === 100;
	}

	/**
	 * Get count of completed tasks in plan
	 *
	 * @return int
	 */
	public function get_completed_tasks_count(): int {
		$count = 0;
		foreach ( $this->tracks as $track ) {
			$count += $track->get_completed_tasks_count();
		}
		return $count;
	}

	/**
	 * Get total tasks count in plan
	 *
	 * @return int
	 */
	public function get_total_tasks_count(): int {
		$count = 0;
		foreach ( $this->tracks as $track ) {
			$count += $track->get_total_tasks_count();
		}
		return $count;
	}

	/**
	 * Get count of completed tracks
	 *
	 * @return int
	 */
	public function get_completed_tracks_count(): int {
		return count(
			array_filter(
				$this->tracks,
				function ( Track $track ) {
					return $track->is_completed();
				}
			)
		);
	}

	/**
	 * Get total tracks count
	 *
	 * @return int
	 */
	public function get_total_tracks_count(): int {
		return count( $this->tracks );
	}

	/**
	 * Get count of completed sections in plan
	 *
	 * @return int
	 */
	public function get_completed_sections_count(): int {
		$count = 0;
		foreach ( $this->tracks as $track ) {
			$count += $track->get_completed_sections_count();
		}
		return $count;
	}

	/**
	 * Get total sections count in plan
	 *
	 * @return int
	 */
	public function get_total_sections_count(): int {
		$count = 0;
		foreach ( $this->tracks as $track ) {
			$count += $track->get_total_sections_count();
		}
		return $count;
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
	public function update_task_status( string $track_id, string $section_id, string $task_id, string $status ): bool {
		$track = $this->get_track( $track_id );
		if ( ! $track ) {
			return false;
		}

		return $track->update_task_status( $section_id, $task_id, $status );
	}

	/**
	 * Update section open state
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param bool   $open Open state
	 * @return bool
	 */
	public function update_section_open_state( string $track_id, string $section_id, bool $open ): bool {
		$track = $this->get_track( $track_id );
		if ( ! $track ) {
			return false;
		}

		return $track->update_section_open_state( $section_id, $open );
	}

	/**
	 * Update status for a section
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $status New status
	 * @return bool
	 */
	public function update_section_status( string $track_id, string $section_id, string $status ): bool {
		$track = $this->get_track( $track_id );
		if ( ! $track ) {
			return false;
		}

		$section = $track->get_section( $section_id );

		if ( ! $section ) {
			return false;
		}

		return $section->set_status( $status );
	}

	/**
	 * Update track open state
	 *
	 * @param string $track_id Track ID
	 * @param bool   $open Open state
	 * @return bool
	 */
	public function update_track_open_state( string $track_id, bool $open ): bool {
		$track = $this->get_track( $track_id );
		if ( ! $track ) {
			return false;
		}

		return $track->set_open( $open );
	}

	/**
	 * Update status for a section
	 *
	 * @param string $track_id Track ID
	 * @param string $section_id Section ID
	 * @param string $status New status
	 * @return bool
	 */
	public function update_status_for_section( string $track_id, string $section_id, string $status ): bool {

		$track = $this->get_track( $track_id );
		if ( ! $track ) {
			return false;
		}

		$section = $track->get_section( $section_id );

		if ( ! $section ) {
			return false;
		}
		// date_completed logic managed in Section DTO
		return $section->update_status( $status );
	}

	/**
	 * Validate plan data
	 *
	 * @return bool|string True if valid, error message if not
	 */
	public function validate() {
		if ( empty( $this->id ) ) {
			return 'Plan ID is required';
		}

		if ( empty( $this->label ) ) {
			return 'Plan label is required';
		}

		// Validate all tracks
		foreach ( $this->tracks as $track ) {
			$track_validation = $track->validate();
			if ( true !== $track_validation ) {
				return "Track validation failed: {$track_validation}";
			}
		}

		return true;
	}
}
