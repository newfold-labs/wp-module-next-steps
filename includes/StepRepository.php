<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * StepRepository class
 *
 * This class is responsible for managing the steps in the Next Steps module.
 * It provides methods to get all steps, save all steps, and mark a step as complete.
 */
class StepRepository {
	const OPTION_KEY = 'nfd_next_steps';

	/**
	 * Get all steps from the options.
	 *
	 * @return Step[] An array of Step objects.
	 */
	public function get_all(): array {
		$raw = get_option( self::OPTION_KEY, array() );
		return array_map( array( Step::class, 'from_array' ), $raw );
	}

	/**
	 * Save all steps to the options.
	 *
	 * @param Step[] $steps An array of Step objects to save.
	 */
	public function save_all( array $steps ): void {
		$encoded = array_map(
			function ( $s ) {
				$s->to_array();
			},
			$steps
		);
		update_option( self::OPTION_KEY, $encoded );
	}

	/**
	 * Mark a step as complete by its ID.
	 *
	 * @param string $id The ID of the step to mark as complete.
	 */
	public function mark_complete( string $id ): void {
		$steps = $this->get_all();
		foreach ( $steps as $step ) {
			if ( $step->id === $id ) {
				$step->status = 'complete';
				break;
			}
		}
		$this->save_all( $steps );
	}
}
