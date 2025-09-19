<?php

namespace NewfoldLabs\WP\Module\NextSteps\Tests\WPUnit;

/**
 * Test Plan for Unit Testing
 *
 * This is a simple, complete plan structure designed specifically for testing
 * all functionality without being tied to production plan data. It provides:
 * - Multiple tracks for testing track-level operations
 * - Multiple sections per track for testing section-level operations
 * - Multiple tasks per section for testing task-level operations
 * - Predictable IDs and structure for reliable test assertions
 */
class TestPlan {

	/**
	 * Get a complete test plan structure
	 *
	 * @return array Complete plan data structure
	 */
	public static function get_plan_data(): array {
		return array(
			'id'          => 'test_plan',
			'type'        => 'custom', // Use custom type for full TestPlan functionality
			'label'       => 'Test Plan',
			'description' => 'A plan designed for testing all functionality',
			// version will be set to current module version by Plan constructor
			'tracks'      => array(
				self::get_track_1_data(),
				self::get_track_2_data(),
			),
		);
	}

	/**
	 * Get first track data (Track A)
	 *
	 * @return array Track data
	 */
	public static function get_track_1_data(): array {
		return array(
			'id'          => 'test_track_a',
			'label'       => 'Test Track A',
			'description' => 'First test track',
			'open'        => false, // Default closed
			'sections'    => array(
				self::get_section_1_data(),
				self::get_section_2_data(),
			),
		);
	}

	/**
	 * Get second track data (Track B)
	 *
	 * @return array Track data
	 */
	public static function get_track_2_data(): array {
		return array(
			'id'          => 'test_track_b',
			'label'       => 'Test Track B',
			'description' => 'Second test track',
			'open'        => false, // Default closed
			'sections'    => array(
				self::get_section_3_data(),
			),
		);
	}

	/**
	 * Get first section data (Section 1)
	 *
	 * @return array Section data
	 */
	public static function get_section_1_data(): array {
		return array(
			'id'             => 'test_section_1',
			'label'          => 'Test Section 1',
			'description'    => 'First test section',
			'open'           => true, // Default open
			'status'         => 'new',
			'date_completed' => null,
			'tasks'          => array(
				self::get_task_1_data(),
				self::get_task_2_data(),
			),
		);
	}

	/**
	 * Get second section data (Section 2)
	 *
	 * @return array Section data
	 */
	public static function get_section_2_data(): array {
		return array(
			'id'             => 'test_section_2',
			'label'          => 'Test Section 2',
			'description'    => 'Second test section',
			'open'           => true, // Default open
			'status'         => 'new',
			'date_completed' => null,
			'tasks'          => array(
				self::get_task_3_data(),
			),
		);
	}

	/**
	 * Get third section data (Section 3)
	 *
	 * @return array Section data
	 */
	public static function get_section_3_data(): array {
		return array(
			'id'             => 'test_section_3',
			'label'          => 'Test Section 3',
			'description'    => 'Third test section',
			'open'           => true, // Default open
			'status'         => 'new',
			'date_completed' => null,
			'tasks'          => array(
				self::get_task_4_data(),
				self::get_task_5_data(),
			),
		);
	}

	/**
	 * Get first task data
	 *
	 * @return array Task data
	 */
	public static function get_task_1_data(): array {
		return array(
			'id'              => 'test_task_1',
			'title'           => 'Test Task 1',
			'description'     => 'First test task',
			'href'            => '/test-task-1',
			'status'          => 'new',
			'priority'        => 1,
			'source'          => 'test',
			'data_attributes' => array(
				'data-test-id' => 'test_task_1',
			),
		);
	}

	/**
	 * Get second task data
	 *
	 * @return array Task data
	 */
	public static function get_task_2_data(): array {
		return array(
			'id'              => 'test_task_2',
			'title'           => 'Test Task 2',
			'description'     => 'Second test task',
			'href'            => '/test-task-2',
			'status'          => 'new',
			'priority'        => 2,
			'source'          => 'test',
			'data_attributes' => array(
				'data-test-id' => 'test_task_2',
			),
		);
	}

	/**
	 * Get third task data
	 *
	 * @return array Task data
	 */
	public static function get_task_3_data(): array {
		return array(
			'id'              => 'test_task_3',
			'title'           => 'Test Task 3',
			'description'     => 'Third test task',
			'href'            => '/test-task-3',
			'status'          => 'new',
			'priority'        => 1,
			'source'          => 'test',
			'data_attributes' => array(
				'data-test-id' => 'test_task_3',
			),
		);
	}

	/**
	 * Get fourth task data
	 *
	 * @return array Task data
	 */
	public static function get_task_4_data(): array {
		return array(
			'id'              => 'test_task_4',
			'title'           => 'Test Task 4',
			'description'     => 'Fourth test task',
			'href'            => '/test-task-4',
			'status'          => 'new',
			'priority'        => 1,
			'source'          => 'test',
			'data_attributes' => array(
				'data-test-id' => 'test_task_4',
			),
		);
	}

	/**
	 * Get fifth task data
	 *
	 * @return array Task data
	 */
	public static function get_task_5_data(): array {
		return array(
			'id'              => 'test_task_5',
			'title'           => 'Test Task 5',
			'description'     => 'Fifth test task',
			'href'            => '/test-task-5',
			'status'          => 'new',
			'priority'        => 2,
			'source'          => 'test',
			'data_attributes' => array(
				'data-test-id' => 'test_task_5',
			),
		);
	}

	/**
	 * Get a minimal test plan with only one track, one section, one task
	 * Useful for testing basic functionality
	 *
	 * @return array Minimal plan data
	 */
	public static function get_minimal_plan_data(): array {
		return array(
			'id'          => 'test_plan_minimal',
			'type'        => 'custom', // Use custom type for full TestPlan functionality
			'label'       => 'Minimal Test Plan',
			'description' => 'A minimal plan for basic testing',
			'version'     => '1.0.0',
			'tracks'      => array(
				array(
					'id'          => 'test_track_minimal',
					'label'       => 'Minimal Track',
					'description' => 'Minimal test track',
					'open'        => false,
					'sections'    => array(
						array(
							'id'             => 'test_section_minimal',
							'label'          => 'Minimal Section',
							'description'    => 'Minimal test section',
							'open'           => true,
							'status'         => 'new',
							'date_completed' => null,
							'tasks'          => array(
								array(
									'id'              => 'test_task_minimal',
									'title'           => 'Minimal Task',
									'description'     => 'Minimal test task',
									'href'            => '/minimal-task',
									'status'          => 'new',
									'priority'        => 1,
									'source'          => 'test',
									'data_attributes' => array(
										'data-test-id' => 'test_task_minimal',
									),
								),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Get a test plan with user progress (for testing merge functionality)
	 *
	 * @return array Plan data with user progress
	 */
	public static function get_plan_with_progress_data(): array {
		$plan_data = self::get_plan_data();

		// Modify track states
		$plan_data['tracks'][0]['open'] = true; // User opened track A
		$plan_data['tracks'][1]['open'] = false; // User closed track B

		// Modify section states
		$plan_data['tracks'][0]['sections'][0]['open']           = true;
		$plan_data['tracks'][0]['sections'][0]['status']         = 'completed';
		$plan_data['tracks'][0]['sections'][0]['date_completed'] = '2024-01-01 12:00:00';

		$plan_data['tracks'][0]['sections'][1]['open']   = false;
		$plan_data['tracks'][0]['sections'][1]['status'] = 'in_progress';

		$plan_data['tracks'][1]['sections'][0]['open']   = true;
		$plan_data['tracks'][1]['sections'][0]['status'] = 'new';

		// Modify task states
		$plan_data['tracks'][0]['sections'][0]['tasks'][0]['status'] = 'done';
		$plan_data['tracks'][0]['sections'][0]['tasks'][1]['status'] = 'done';
		$plan_data['tracks'][0]['sections'][1]['tasks'][0]['status'] = 'in_progress';
		$plan_data['tracks'][1]['sections'][0]['tasks'][0]['status'] = 'new';
		$plan_data['tracks'][1]['sections'][0]['tasks'][1]['status'] = 'new';

		return $plan_data;
	}

	/**
	 * Get a test plan with old version (for testing version updates)
	 *
	 * @return array Plan data with old version
	 */
	public static function get_old_version_plan_data(): array {
		$plan_data            = self::get_plan_data();
		$plan_data['version'] = '0.9.0'; // Old version
		return $plan_data;
	}

	/**
	 * Get expected structure summary for assertions
	 *
	 * @return array Structure summary
	 */
	public static function get_structure_summary(): array {
		return array(
			'tracks'   => 2,
			'sections' => 3, // 2 in track A, 1 in track B
			'tasks'    => 5, // 2 in section 1, 1 in section 2, 2 in section 3
		);
	}
}
