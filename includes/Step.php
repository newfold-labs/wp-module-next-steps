<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * Step class
 *
 * Simple DTO for each step in the Next Steps module.
 */
class Step {

	/**
	 * Constructor
	 * 
	 * @param string $id The unique identifier for the step.
	 * @param string $title The title of the step.
	 * @param string $description A description of the step.
	 * @param int $priority The priority of the step, used for ordering.
	 * @param string $status The status of the step (e.g., 'pending', 'complete').
	 * @param string $href The URL to navigate to for this step.
	 */
	public function __construct(
		public string $id,
		public string $title,
		public string $description,
		public int $priority,
		public string $status,
		public string $href,
	) {}

	/**
	 * Create a Step instance from an array.
	 *
	 * @param array $data The data to create the Step from.
	 * @return Step The created Step instance.
	 */
	public static function from_array( array $data ): self {
		return new self(
			$data['id'],
			$data['title'],
			$data['description'],
			$data['priority'],
			$data['status'],
			$data['href']
		);
	}

	/**
	 * Convert the Step instance to an array.
	 *
	 * @return array The Step instance as an array.
	 */
	public function to_array(): array {
		return get_object_vars( $this );
	}
}
