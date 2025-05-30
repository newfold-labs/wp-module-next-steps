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
	public static function from_array(array $data): self {
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
		return get_object_vars($this);
	}
}
