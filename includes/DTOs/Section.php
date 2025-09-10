<?php

namespace NewfoldLabs\WP\Module\NextSteps\DTOs;

/**
 * Section Data Transfer Object
 *
 * Represents a section that contains multiple tasks
 */
class Section {

	/**
	 * Section identifier
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Section label
	 *
	 * @var string
	 */
	public $label;

	/**
	 * Section description
	 *
	 * @var string
	 */
	public $description;

	/**
	 * Section state (open or closed)
	 *
	 * @var boolean
	 */
	public $open;

	/**
	 * Section tasks
	 *
	 * @var Task[]
	 */
	public $tasks;

	/**
	 * Call-to-action (CTA) for the section.
	 *
	 * @var mixed|null
	 */
	public $cta;

	/**
	 * Status of the section (e.g. 'new', 'done', 'dismissed'
	 *
	 * @var string
	 */
	public $status;

	/**
	 * Date when the section was completed or dismissed.
	 *
	 * @var string|null
	 */
	public $date_completed;

	/**
	 * Icon associated with the section.
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * Title for the modal related to the section.
	 *
	 * @var string
	 */
	public $modal_title;

	/**
	 * Description for the modal related to the section.
	 *
	 * @var string
	 */
	public $modal_desc;

	/**
	 * Section constructor
	 *
	 * @param array $data Section data
	 */
	public function __construct( array $data = array() ) {
		$this->id             = $data['id'] ?? '';
		$this->label          = $data['label'] ?? '';
		$this->description    = $data['description'] ?? '';
		$this->open           = $data['open'] ?? false;
		$this->tasks          = array();
		$this->cta            = $data['cta'] ?? null;
		$this->status         = $data['status'] ?? 'new';
		$this->date_completed = $data['date_completed'] ?? null;
		$this->icon           = $data['icon'] ?? '';
		$this->modal_title    = $data['modal_title'] ?? '';
		$this->modal_desc     = $data['modal_desc'] ?? '';

		// Convert task arrays to Task objects
		if ( isset( $data['tasks'] ) && is_array( $data['tasks'] ) ) {
			foreach ( $data['tasks'] as $task_data ) {
				if ( $task_data instanceof Task ) {
					$this->tasks[] = $task_data;
				} else {
					$this->tasks[] = Task::from_array( $task_data );
				}
			}
		}
	}

	/**
	 * Convert Section to array
	 *
	 * @return array
	 */
	public function to_array(): array {
		return array(
			'id'             => $this->id,
			'label'          => $this->label,
			'description'    => $this->description,
			'open'           => $this->open,
			'cta'            => $this->cta,
			'status'         => $this->status,
			'date_completed' => $this->date_completed,
			'icon'           => $this->icon,
			'modal_title'    => $this->modal_title,
			'modal_desc'     => $this->modal_desc,
			'tasks'          => array_map(
				function ( Task $task ) {
					return $task->to_array();
				},
				$this->tasks
			),
		);
	}

	/**
	 * Create Section from array
	 *
	 * @param array $data Section data
	 * @return Section
	 */
	public static function from_array( array $data ): Section {
		return new self( $data );
	}

	/**
	 * Add task to section
	 *
	 * @param Task $task Task to add
	 * @return bool
	 */
	public function add_task( Task $task ): bool {
		// Check if task with same ID already exists
		foreach ( $this->tasks as $existing_task ) {
			if ( $existing_task->id === $task->id ) {
				return false; // Task already exists
			}
		}

		$this->tasks[] = $task;
		$this->sort_tasks();
		return true;
	}

	/**
	 * Remove task from section
	 *
	 * @param string $task_id Task ID to remove
	 * @return bool
	 */
	public function remove_task( string $task_id ): bool {
		foreach ( $this->tasks as $index => $task ) {
			if ( $task->id === $task_id ) {
				unset( $this->tasks[ $index ] );
				$this->tasks = array_values( $this->tasks ); // Reindex
				return true;
			}
		}
		return false;
	}

	/**
	 * Get task by ID
	 *
	 * @param string $task_id Task ID
	 * @return Task|null
	 */
	public function get_task( string $task_id ): ?Task {
		foreach ( $this->tasks as $task ) {
			if ( $task->id === $task_id ) {
				return $task;
			}
		}
		return null;
	}

	/**
	 * Update task status
	 *
	 * @param string $task_id Task ID
	 * @param string $status New status
	 * @return bool
	 */
	public function update_task_status( string $task_id, string $status ): bool {
		$task = $this->get_task( $task_id );
		if ( $task ) {
			return $task->update_status( $status );
		}
		return false;
	}

	/**
	 * Update section status
	 *
	 * @param string $status New status
	 * @return bool
	 */
	public function update_status( string $status ): bool {
		if ( ! in_array( $status, array( 'new', 'dismissed', 'done' ), true ) ) {
			return false;
		}
		$this->status = $status;
		// automatically record completed/dismissed 
		if ( in_array( $status, array( 'dismissed', 'done' ), true ) ) {
			$this->set_completed_now();
		} else {
			// reset date completed if marked as new
			$this->clear_completed_date();
		}

		return true;
	}

	/**
	 * Set completed now
	 */
	public function set_completed_now(): bool {
		$now = new \DateTime( 'now', new \DateTimeZone( wp_timezone_string() ) );
		$this->set_date_completed( $now->format( 'Y-m-d H:i:s' ) );
		return true;
	}

	/**
	 * Clear completed date
	 */
	public function clear_completed_date(): bool {
		$this->set_date_completed( null );
		return true;
	}

	/**
	 * Get all tasks
	 *
	 * @return Task[]
	 */
	public function get_tasks(): array {
		return $this->tasks;
	}

	/**
	 * Sort tasks by priority
	 */
	public function sort_tasks(): void {
		usort(
			$this->tasks,
			function ( Task $a, Task $b ) {
				return $a->priority <=> $b->priority;
			}
		);
	}

	/**
	 * Get section completion percentage
	 *
	 * @return int
	 */
	public function get_completion_percentage(): int {
		if ( empty( $this->tasks ) ) {
			return 0;
		}

		$completed_tasks = array_filter(
			$this->tasks,
			function ( Task $task ) {
				return $task->is_completed();
			}
		);

		return intval( ( count( $completed_tasks ) / count( $this->tasks ) ) * 100 );
	}

	/**
	 * Check if section is completed
	 *
	 * @return bool
	 */
	public function is_completed(): bool {
		return $this->get_completion_percentage() === 100;
	}

	/**
	 * Get count of completed tasks
	 *
	 * @return int
	 */
	public function get_completed_tasks_count(): int {
		return count(
			array_filter(
				$this->tasks,
				function ( Task $task ) {
					return $task->is_completed();
				}
			)
		);
	}

	/**
	 * Get total tasks count
	 *
	 * @return int
	 */
	public function get_total_tasks_count(): int {
		return count( $this->tasks );
	}

	/**
	 * Set section open state
	 *
	 * @param bool $open Open state
	 * @return bool
	 */
	public function set_open( bool $open ): bool {
		$this->open = $open;
		return true;
	}

	/**
	 * Set section status state
	 *
	 * @param string $status Status state
	 * @return bool
	 */
	public function set_status( string $status ): bool {
		$this->status = $status;
		return true;
	}

	/**
	 * Set date completed or dismissed
	 *
	 * @param string|null $date Date string or null
	 * @return bool
	 */
	public function set_date_completed( ?string $date ): bool {
		$this->date_completed = $date;
		return true;
	}

	/**
	 * Check if section is open
	 *
	 * @return bool
	 */
	public function is_open(): bool {
		return $this->open;
	}

	/**
	 * Toggle section open state
	 *
	 * @return bool New open state
	 */
	public function toggle_open(): bool {
		$this->open = ! $this->open;
		return $this->open;
	}

	/**
	 * Validate section data
	 *
	 * @return bool|string True if valid, error message if not
	 */
	public function validate() {
		if ( empty( $this->id ) ) {
			return 'Section ID is required';
		}

		if ( empty( $this->label ) ) {
			return 'Section label is required';
		}

		// Validate all tasks
		foreach ( $this->tasks as $task ) {
			$task_validation = $task->validate();
			if ( true !== $task_validation ) {
				return "Task validation failed: {$task_validation}";
			}
		}

		return true;
	}
}
