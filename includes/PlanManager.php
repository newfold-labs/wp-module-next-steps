<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Track;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;

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
	 * Option name for the solution type
	 */
	const SOLUTION_OPTION = 'nfd_solution';

	/**
	 * Available plan types
	 */
	const PLAN_TYPES = array(
		'ecommerce' => 'ecommerce',
		'blog'      => 'blog',
		'corporate' => 'corporate',
	);

	/**
	 * Get the current plan
	 *
	 * @return Plan|null
	 */
	public static function get_current_plan(): ?Plan {
		$plan_data = get_option( self::OPTION, array() );
		$plan_data = false; // for resetting data while debugging
		if ( empty( $plan_data ) ) {
			// Load default plan based on solution
			return self::load_default_plan();
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
		return update_option( self::OPTION, $plan->to_array() );
	}

	/**
	 * Load default plan based on solution
	 *
	 * @return Plan
	 */
	public static function load_default_plan(): Plan {
		$solution = get_option( self::SOLUTION_OPTION, 'ecommerce' );
		
		switch ( $solution ) {
			case 'blog':
				$plan = self::get_blog_plan();
				break;
			case 'corporate':
				$plan = self::get_corporate_plan();
				break;
			case 'ecommerce':
			default:
				$plan = self::get_ecommerce_plan();
				break;
		}

		// Save the loaded plan
		self::save_plan( $plan );
		
		return $plan;
	}

	/**
	 * Switch to a different plan type
	 *
	 * @param string $plan_type Plan type to switch to
	 * @return Plan|false
	 */
	public static function switch_plan( string $plan_type ) {
		if ( ! in_array( $plan_type, self::PLAN_TYPES, true ) ) {
			return false;
		}

		// Update the solution option
		update_option( self::SOLUTION_OPTION, $plan_type );

		// Load the new plan
		delete_option( self::OPTION ); // Clear current plan
		return self::load_default_plan();
	}

	/**
	 * Get ecommerce plan
	 *
	 * @return Plan
	 */
	public static function get_ecommerce_plan(): Plan {
		$plan_data = DefaultSteps::get_store_setup_data();
		return Plan::from_array( $plan_data['plan'] );
	}

	/**
	 * Get blog plan
	 *
	 * @return Plan
	 */
	public static function get_blog_plan(): Plan {
		// TODO: Implement blog plan data
		return new Plan( array(
			'id'          => 'blog_setup',
			'label'       => __( 'Blog Setup', 'wp-module-next-steps' ),
			'description' => __( 'Get your blog up and running:', 'wp-module-next-steps' ),
			'tracks'      => array(
				array(
					'id'       => 'blog_content_track',
					'label'    => __( 'Step 1: Content', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'basic_content',
							'label'       => __( 'Basic Content Setup', 'wp-module-next-steps' ),
							'description' => __( 'Create your first content', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'blog_first_post',
									'title'       => __( 'Write your first blog post', 'wp-module-next-steps' ),
									'description' => __( 'Create your first blog post to start sharing your thoughts', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'blog_about_page',
									'title'       => __( 'Create an About page', 'wp-module-next-steps' ),
									'description' => __( 'Tell your visitors who you are', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
			),
		) );
	}

	/**
	 * Get corporate plan
	 *
	 * @return Plan
	 */
	public static function get_corporate_plan(): Plan {
		// TODO: Implement corporate plan data
		return new Plan( array(
			'id'          => 'corporate_setup',
			'label'       => __( 'Corporate Setup', 'wp-module-next-steps' ),
			'description' => __( 'Set up your corporate website:', 'wp-module-next-steps' ),
			'tracks'      => array(
				array(
					'id'       => 'corporate_content_track',
					'label'    => __( 'Step 1: Content', 'wp-module-next-steps' ),
					'sections' => array(
						array(
							'id'          => 'basic_pages',
							'label'       => __( 'Basic Pages', 'wp-module-next-steps' ),
							'description' => __( 'Create essential corporate pages', 'wp-module-next-steps' ),
							'tasks'       => array(
								array(
									'id'          => 'corporate_about_page',
									'title'       => __( 'Create About Us page', 'wp-module-next-steps' ),
									'description' => __( 'Tell visitors about your company', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 1,
									'source'      => 'wp-module-next-steps',
								),
								array(
									'id'          => 'corporate_contact_page',
									'title'       => __( 'Create Contact page', 'wp-module-next-steps' ),
									'description' => __( 'Make it easy for people to contact you', 'wp-module-next-steps' ),
									'href'        => '{siteUrl}/wp-admin/post-new.php?post_type=page',
									'status'      => 'new',
									'priority'    => 2,
									'source'      => 'wp-module-next-steps',
								),
							),
						),
					),
				),
			),
		) );
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
	 * @param Task $task Task to add
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
		return self::load_default_plan();
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
			'completion_percentage'    => $plan->get_completion_percentage(),
			'total_tasks'              => $plan->get_total_tasks_count(),
			'completed_tasks'          => $plan->get_completed_tasks_count(),
			'total_sections'           => $plan->get_total_sections_count(),
			'completed_sections'       => $plan->get_completed_sections_count(),
			'total_tracks'             => $plan->get_total_tracks_count(),
			'completed_tracks'         => $plan->get_completed_tracks_count(),
			'is_completed'             => $plan->is_completed(),
		);
	}
} 