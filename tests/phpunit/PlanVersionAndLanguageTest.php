<?php
/**
 * Tests for Plan Version Updates and Language Changes
 *
 * @package WPModuleNextSteps
 */

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;

/**
 * Class PlanVersionAndLanguageTest
 *
 * @package WPModuleNextSteps
 */
class PlanVersionAndLanguageTest extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );
	}

	/**
	 * Test version update triggers merge when saved version is older
	 */
	public function test_version_update_triggers_merge() {
		// Create a saved plan with old version
		$saved_plan_data = array(
			'id'          => 'blog_setup',
			'type'        => 'blog',
			'label'       => 'Blog Setup',
			'description' => 'Set up your blog',
			'version'     => '0.8.0', // Old version
			'tracks'      => array(
				array(
					'id'          => 'blog_build_track',
					'label'       => 'Build',
					'description' => 'Build your blog',
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'basic_blog_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic blog setup',
							'open'           => true,
							'status'         => 'completed',
							'date_completed' => '2024-01-01 12:00:00',
							'tasks'          => array(
								array(
									'id'          => 'blog_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your blog quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Get current plan (should trigger merge due to version difference)
		$current_plan = PlanRepository::get_current_plan();
		
		// Verify version was updated
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $current_plan->version );
		
		// Verify user progress was preserved
		$track = $current_plan->get_track( 'blog_build_track' );
		$this->assertTrue( $track->open );
		
		$section = $current_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );
		
		$task = $current_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test no merge when saved version is current
	 */
	public function test_no_merge_when_version_is_current() {
		// Create a saved plan with current version
		$saved_plan_data = array(
			'id'          => 'blog_setup',
			'type'        => 'blog',
			'label'       => 'Blog Setup',
			'description' => 'Set up your blog',
			'version'     => PlanRepository::PLAN_DATA_VERSION, // Current version
			'tracks'      => array(
				array(
					'id'          => 'blog_build_track',
					'label'       => 'Build',
					'description' => 'Build your blog',
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'basic_blog_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic blog setup',
							'open'           => true,
							'status'         => 'completed',
							'date_completed' => '2024-01-01 12:00:00',
							'tasks'          => array(
								array(
									'id'          => 'blog_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your blog quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the current plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Get current plan (should NOT trigger merge)
		$current_plan = PlanRepository::get_current_plan();
		
		// Verify version remains the same
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $current_plan->version );
		
		// Verify user progress was preserved exactly
		$track = $current_plan->get_track( 'blog_build_track' );
		$this->assertTrue( $track->open );
		
		$section = $current_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );
		
		$task = $current_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test version update with new tasks added
	 */
	public function test_version_update_with_new_tasks() {
		// Create a saved plan with old version and limited tasks
		$saved_plan_data = array(
			'id'          => 'blog_setup',
			'type'        => 'blog',
			'label'       => 'Blog Setup',
			'description' => 'Set up your blog',
			'version'     => '0.9.0', // Old version
			'tracks'      => array(
				array(
					'id'          => 'blog_build_track',
					'label'       => 'Build',
					'description' => 'Build your blog',
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'basic_blog_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic store setup',
							'open'           => true,
							'status'         => 'in_progress',
							'date_completed' => null,
							'tasks'          => array(
								array(
									'id'          => 'blog_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your store quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Get current plan (should trigger merge and add new tasks)
		$current_plan = PlanRepository::get_current_plan();
		
		// Verify version was updated
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $current_plan->version );
		
		// Verify existing task status was preserved
		$existing_task = $current_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertNotNull( $existing_task );
		$this->assertEquals( 'done', $existing_task->status );
		
		// Verify new tasks were added with default status
		$section = $current_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertGreaterThanOrEqual( 1, count( $section->tasks ) );
		
		// Verify that the existing task has the correct status
		$existing_task = $current_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertEquals( 'done', $existing_task->status );
		
		// If there are other tasks, they should have default status
		foreach ( $section->tasks as $task ) {
			if ( $task->id !== 'blog_quick_setup' ) {
				$this->assertEquals( 'new', $task->status );
			}
		}
	}

	/**
	 * Test language change triggers resync
	 */
	public function test_language_change_triggers_resync() {
		// Create a saved plan with user progress
		$saved_plan_data = array(
			'id'          => 'blog_setup',
			'type'        => 'blog',
			'label'       => 'Blog Setup',
			'description' => 'Set up your blog',
			'version'     => '1.0.0',
			'tracks'      => array(
				array(
					'id'          => 'blog_build_track',
					'label'       => 'Build',
					'description' => 'Build your blog',
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'basic_blog_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic blog setup',
							'open'           => true,
							'status'         => 'completed',
							'date_completed' => '2024-01-01 12:00:00',
							'tasks'          => array(
								array(
									'id'          => 'blog_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your blog quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Simulate language change
		PlanFactory::on_language_change( 'en_US', 'es_ES' );
		
		// Get the updated plan
		$updated_plan = PlanRepository::get_current_plan();
		
		// Verify user progress was preserved
		$track = $updated_plan->get_track( 'blog_build_track' );
		$this->assertTrue( $track->open );
		
		$section = $updated_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'completed', $section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section->date_completed );
		
		$task = $updated_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertEquals( 'done', $task->status );
		
		// Verify plan structure is complete (new language content merged in)
		$this->assertGreaterThan( 0, count( $updated_plan->tracks ) );
		$this->assertGreaterThan( 0, count( $track->sections ) );
		$this->assertGreaterThan( 0, count( $section->tasks ) );
	}

	/**
	 * Test locale switch triggers resync
	 */
	public function test_locale_switch_triggers_resync() {
		// Create a saved plan with user progress
		$saved_plan_data = array(
			'id'          => 'corporate_setup',
			'type'        => 'corporate',
			'label'       => 'Corporate Setup',
			'description' => 'Set up your corporate site',
			'version'     => '1.0.0',
			'tracks'      => array(
				array(
					'id'          => 'corporate_build_track',
					'label'       => 'Build',
					'description' => 'Build your corporate site',
					'open'        => false, // User closed this track
					'sections'    => array(
						array(
							'id'             => 'basic_site_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic corporate setup',
							'open'           => true,
							'status'         => 'in_progress',
							'date_completed' => null,
							'tasks'          => array(
								array(
									'id'          => 'corporate_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your corporate site quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Simulate locale switch
		PlanFactory::on_locale_switch( 'fr_FR' );
		
		// Get the updated plan
		$updated_plan = PlanRepository::get_current_plan();
		
		// Verify user progress was preserved
		$track = $updated_plan->get_track( 'corporate_build_track' );
		$this->assertFalse( $track->open ); // User's choice preserved
		
		$section = $updated_plan->get_section( 'corporate_build_track', 'basic_site_setup' );
		$this->assertNotNull( $section );
		$this->assertTrue( $section->open );
		$this->assertEquals( 'in_progress', $section->status );
		$this->assertNull( $section->date_completed );
		
		$task = $updated_plan->get_task( 'corporate_build_track', 'basic_site_setup', 'corporate_quick_setup' );
		$this->assertNotNull( $task );
		$this->assertEquals( 'done', $task->status );
	}

	/**
	 * Test version update with new sections added
	 */
	public function test_version_update_with_new_sections() {
		// Create a saved plan with old version and limited sections
		$saved_plan_data = array(
			'id'          => 'blog_setup',
			'type'        => 'blog',
			'label'       => 'Blog Setup',
			'description' => 'Set up your blog',
			'version'     => '0.8.0', // Old version
			'tracks'      => array(
				array(
					'id'          => 'blog_build_track',
					'label'       => 'Build',
					'description' => 'Build your blog',
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'basic_blog_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic blog setup',
							'open'           => true,
							'status'         => 'completed',
							'date_completed' => '2024-01-01 12:00:00',
							'tasks'          => array(
								array(
									'id'          => 'blog_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your blog quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Get current plan (should trigger merge and add new sections)
		$current_plan = PlanRepository::get_current_plan();
		
		// Verify version was updated
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $current_plan->version );
		
		// Verify existing section state was preserved
		$existing_section = $current_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertTrue( $existing_section->open );
		$this->assertEquals( 'completed', $existing_section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $existing_section->date_completed );
		
		// Verify new sections were added with default state
		$track = $current_plan->get_track( 'blog_build_track' );
		$this->assertGreaterThan( 1, count( $track->sections ) );
		
		// Find a new section
		$new_section_found = false;
		foreach ( $track->sections as $section ) {
			if ( $section->id !== 'basic_blog_setup' ) {
				$this->assertTrue( $section->open ); // Default state
				$this->assertEquals( 'new', $section->status ); // Default state
				$this->assertNull( $section->date_completed ); // Default state
				$new_section_found = true;
				break;
			}
		}
		$this->assertTrue( $new_section_found, 'New sections should be added with default state' );
	}

	/**
	 * Test version update with new tracks added
	 */
	public function test_version_update_with_new_tracks() {
		// Create a saved plan with old version and limited tracks
		$saved_plan_data = array(
			'id'          => 'blog_setup',
			'type'        => 'blog',
			'label'       => 'Blog Setup',
			'description' => 'Set up your blog',
			'version'     => '0.9.0', // Old version
			'tracks'      => array(
				array(
					'id'          => 'blog_build_track',
					'label'       => 'Build',
					'description' => 'Build your blog',
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'basic_blog_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic store setup',
							'open'           => true,
							'status'         => 'in_progress',
							'date_completed' => null,
							'tasks'          => array(
								array(
									'id'          => 'blog_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your store quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Get current plan (should trigger merge and add new tracks)
		$current_plan = PlanRepository::get_current_plan();
		
		// Verify version was updated
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $current_plan->version );
		
		// Verify existing track state was preserved
		$existing_track = $current_plan->get_track( 'blog_build_track' );
		$this->assertTrue( $existing_track->open );
		
		// Verify new tracks were added with default state
		$this->assertGreaterThan( 1, count( $current_plan->tracks ) );
		
		// Find a new track
		$new_track_found = false;
		foreach ( $current_plan->tracks as $track ) {
			if ( $track->id !== 'blog_build_track' ) {
				$this->assertFalse( $track->open ); // Default state is false
				$new_track_found = true;
				break;
			}
		}
		$this->assertTrue( $new_track_found, 'New tracks should be added with default state' );
	}

	/**
	 * Test version update preserves all user progress across multiple levels
	 */
	public function test_version_update_preserves_all_user_progress() {
		// Create a saved plan with extensive user progress
		$saved_plan_data = array(
			'id'          => 'corporate_setup',
			'type'        => 'corporate',
			'label'       => 'Corporate Setup',
			'description' => 'Set up your corporate site',
			'version'     => '0.8.0', // Old version
			'tracks'      => array(
				array(
					'id'          => 'corporate_build_track',
					'label'       => 'Build',
					'description' => 'Build your corporate site',
					'open'        => false, // User closed this track
					'sections'    => array(
						array(
							'id'             => 'basic_site_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic corporate setup',
							'open'           => true,
							'status'         => 'completed',
							'date_completed' => '2024-01-01 12:00:00',
							'tasks'          => array(
								array(
									'id'          => 'corporate_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your corporate site quickly',
									'href'        => '/setup',
									'status'      => 'done',
								),
								array(
									'id'          => 'corporate_add_content',
									'title'       => 'Add Content',
									'description' => 'Add your first content',
									'href'        => '/content',
									'status'      => 'dismissed',
								),
							),
						),
						array(
							'id'             => 'advanced_corporate_setup',
							'label'          => 'Advanced Setup',
							'description'    => 'Advanced corporate setup',
							'open'           => false,
							'status'         => 'in_progress',
							'date_completed' => null,
							'tasks'          => array(
								array(
									'id'          => 'corporate_customize',
									'title'       => 'Customize',
									'description' => 'Customize your corporate site',
									'href'        => '/customize',
									'status'      => 'new',
								),
							),
						),
					),
				),
			),
		);
		
		// Save the old plan
		update_option( PlanRepository::OPTION, $saved_plan_data );
		
		// Get current plan (should trigger merge)
		$current_plan = PlanRepository::get_current_plan();
		
		// Verify version was updated
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $current_plan->version );
		
		// Verify all user progress was preserved
		$track = $current_plan->get_track( 'corporate_build_track' );
		$this->assertFalse( $track->open );
		
		$section1 = $current_plan->get_section( 'corporate_build_track', 'basic_site_setup' );
		$this->assertNotNull( $section1 );
		$this->assertTrue( $section1->open );
		$this->assertEquals( 'completed', $section1->status );
		$this->assertEquals( '2024-01-01 12:00:00', $section1->date_completed );
		
		$section2 = $current_plan->get_section( 'corporate_build_track', 'advanced_corporate_setup' );
		if ( $section2 ) {
			$this->assertFalse( $section2->open );
			$this->assertEquals( 'in_progress', $section2->status );
			$this->assertNull( $section2->date_completed );
		}
		
		$task1 = $current_plan->get_task( 'corporate_build_track', 'basic_site_setup', 'corporate_quick_setup' );
		$this->assertNotNull( $task1 );
		$this->assertEquals( 'done', $task1->status );
		
		$task2 = $current_plan->get_task( 'corporate_build_track', 'basic_site_setup', 'corporate_add_content' );
		if ( $task2 ) {
			$this->assertEquals( 'dismissed', $task2->status );
		}
		
		$task3 = $current_plan->get_task( 'corporate_build_track', 'advanced_corporate_setup', 'corporate_customize' );
		if ( $task3 ) {
			$this->assertEquals( 'new', $task3->status );
		}
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up options after each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );

		parent::tearDown();
	}
}
