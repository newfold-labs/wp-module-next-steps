<?php
/**
 * Tests for Plan Merge Functionality
 *
 * @package WPModuleNextSteps
 */

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Track;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Section;
use NewfoldLabs\WP\Module\NextSteps\DTOs\Task;

/**
 * Class PlanMergeTest
 *
 * @package WPModuleNextSteps
 */
class PlanMergeTest extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
	}

	/**
	 * Test basic plan merge functionality
	 */
	public function test_basic_plan_merge() {
		// Create a new plan
		$new_plan = PlanFactory::create_plan( 'blog' );
		
		// Create a saved plan with some user progress
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
					'open'        => true, // User has opened this track
					'sections'    => array(
						array(
							'id'             => 'basic_blog_setup',
							'label'          => 'Basic Setup',
							'description'    => 'Basic blog setup',
							'open'           => false, // User has closed this section
							'status'         => 'completed', // User has completed this section
							'date_completed' => '2024-01-01 12:00:00',
							'tasks'          => array(
								array(
									'id'          => 'blog_quick_setup',
									'title'       => 'Quick Setup',
									'description' => 'Set up your blog quickly',
									'href'        => '/setup',
									'status'      => 'done', // User has completed this task
								),
							),
						),
					),
				),
			),
		);
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify the merge preserved user progress
		$this->assertEquals( 'blog_setup', $merged_plan->id );
		$this->assertEquals( 'blog', $merged_plan->type );
		
		// Check that track open state was preserved
		$merged_track = $merged_plan->get_track( 'blog_build_track' );
		$this->assertTrue( $merged_track->open );
		
		// Check that section state was preserved
		$merged_section = $merged_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertFalse( $merged_section->open );
		$this->assertEquals( 'completed', $merged_section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $merged_section->date_completed );
		
		// Check that task status was preserved
		$merged_task = $merged_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertEquals( 'done', $merged_task->status );
		
		// Check that version was updated to current version
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $merged_plan->version );
	}

	/**
	 * Test merge with version update scenario
	 */
	public function test_merge_with_version_update() {
		// Create a new plan (current version)
		$new_plan = PlanFactory::create_plan( 'ecommerce' );
		
		// Create a saved plan with old version
		$saved_plan_data = array(
			'id'          => 'store_setup',
			'type'        => 'ecommerce',
			'label'       => 'Store Setup',
			'description' => 'Set up your store',
			'version'     => '0.9.0', // Old version
			'tracks'      => array(
				array(
					'id'          => 'store_build_track',
					'label'       => 'Build',
					'description' => 'Build your store',
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'customize_your_store',
							'label'          => 'Customize Your Store',
							'description'    => 'Customize your store',
							'open'           => true,
							'status'         => 'in_progress',
							'date_completed' => null,
							'tasks'          => array(
								array(
									'id'          => 'store_upload_logo',
									'title'       => 'Upload Logo',
									'description' => 'Upload your store logo',
									'href'        => '/logo',
									'status'      => 'done',
								),
								array(
									'id'          => 'store_add_product',
									'title'       => 'Add Product',
									'description' => 'Add your first product',
									'href'        => '/products',
									'status'      => 'new',
								),
							),
						),
					),
				),
			),
		);
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify version was updated
		$this->assertEquals( PlanRepository::PLAN_DATA_VERSION, $merged_plan->version );
		
		// Verify user progress was preserved
		$merged_track = $merged_plan->get_track( 'store_build_track' );
		$this->assertTrue( $merged_track->open );
		
		$merged_section = $merged_plan->get_section( 'store_build_track', 'customize_your_store' );
		$this->assertNotNull( $merged_section );
		$this->assertTrue( $merged_section->open );
		$this->assertEquals( 'in_progress', $merged_section->status );
		
		$merged_task1 = $merged_plan->get_task( 'store_build_track', 'customize_your_store', 'store_upload_logo' );
		$this->assertEquals( 'done', $merged_task1->status );
		
		$merged_task2 = $merged_plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertNotNull( $merged_task2 );
		$this->assertEquals( 'new', $merged_task2->status );
	}

	/**
	 * Test merge with new tasks added in updated plan
	 */
	public function test_merge_with_new_tasks() {
		// Create a new plan
		$new_plan = PlanFactory::create_plan( 'corporate' );
		
		// Create a saved plan with some progress
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
					'open'        => true,
					'sections'    => array(
						array(
							'id'             => 'basic_site_setup',
							'label'          => 'Basic Site Setup',
							'description'    => 'Basic site setup',
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
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify existing task status was preserved
		$existing_task = $merged_plan->get_task( 'corporate_build_track', 'basic_site_setup', 'corporate_quick_setup' );
		$this->assertNotNull( $existing_task );
		$this->assertEquals( 'done', $existing_task->status );
		
		// Verify new tasks from updated plan are present with default status
		$new_tasks = $merged_plan->get_section( 'corporate_build_track', 'basic_site_setup' )->tasks;
		$this->assertGreaterThanOrEqual( 1, count( $new_tasks ) );
		
		// Verify that the existing task has the correct status
		$existing_task = $merged_plan->get_task( 'corporate_build_track', 'basic_site_setup', 'corporate_quick_setup' );
		$this->assertEquals( 'done', $existing_task->status );
		
		// If there are other tasks, they should have default status
		foreach ( $new_tasks as $task ) {
			if ( $task->id !== 'corporate_quick_setup' ) {
				$this->assertEquals( 'new', $task->status );
			}
		}
	}

	/**
	 * Test merge with new sections added in updated plan
	 */
	public function test_merge_with_new_sections() {
		// Create a new plan
		$new_plan = PlanFactory::create_plan( 'blog' );
		
		// Create a saved plan with only one section
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
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify existing section state was preserved
		$existing_section = $merged_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertTrue( $existing_section->open );
		$this->assertEquals( 'completed', $existing_section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $existing_section->date_completed );
		
		// Verify new sections from updated plan are present with default state
		$all_sections = $merged_plan->get_track( 'blog_build_track' )->sections;
		$this->assertGreaterThan( 1, count( $all_sections ) );
		
		// Find a section that wasn't in the saved plan
		$new_section_found = false;
		foreach ( $all_sections as $section ) {
			if ( $section->id !== 'basic_blog_setup' ) {
				$this->assertTrue( $section->open ); // Default state
				$this->assertEquals( 'new', $section->status ); // Default state
				$this->assertNull( $section->date_completed ); // Default state
				$new_section_found = true;
				break;
			}
		}
		$this->assertTrue( $new_section_found, 'New sections should be present with default state' );
	}

	/**
	 * Test merge with new tracks added in updated plan
	 */
	public function test_merge_with_new_tracks() {
		// Create a new plan
		$new_plan = PlanFactory::create_plan( 'blog' );
		
		// Create a saved plan with only one track
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
							'label'          => 'Basic Blog Setup',
							'description'    => 'Basic blog setup',
							'open'           => true,
							'status'         => 'in_progress',
							'date_completed' => null,
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
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify existing track state was preserved
		$existing_track = $merged_plan->get_track( 'blog_build_track' );
		$this->assertTrue( $existing_track->open );
		
		// Verify new tracks from updated plan are present with default state
		$all_tracks = $merged_plan->tracks;
		$this->assertGreaterThan( 1, count( $all_tracks ) );
		
		// Find a track that wasn't in the saved plan
		$new_track_found = false;
		foreach ( $all_tracks as $track ) {
			if ( $track->id !== 'blog_build_track' ) {
				$this->assertFalse( $track->open ); // Default state is false
				$new_track_found = true;
				break;
			}
		}
		$this->assertTrue( $new_track_found, 'New tracks should be present with default state' );
	}

	/**
	 * Test merge preserves all user progress across multiple levels
	 */
	public function test_merge_preserves_all_user_progress() {
		// Create a new plan
		$new_plan = PlanFactory::create_plan( 'corporate' );
		
		// Create a saved plan with extensive user progress
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
							'label'          => 'Basic Site Setup',
							'description'    => 'Basic site setup',
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
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify track state was preserved
		$merged_track = $merged_plan->get_track( 'corporate_build_track' );
		$this->assertFalse( $merged_track->open );
		
		// Verify first section state was preserved
		$merged_section1 = $merged_plan->get_section( 'corporate_build_track', 'basic_site_setup' );
		$this->assertNotNull( $merged_section1 );
		$this->assertTrue( $merged_section1->open );
		$this->assertEquals( 'completed', $merged_section1->status );
		$this->assertEquals( '2024-01-01 12:00:00', $merged_section1->date_completed );
		
		// Verify second section state was preserved (if it exists)
		$merged_section2 = $merged_plan->get_section( 'corporate_build_track', 'advanced_corporate_setup' );
		if ( $merged_section2 ) {
			$this->assertFalse( $merged_section2->open );
			$this->assertEquals( 'in_progress', $merged_section2->status );
			$this->assertNull( $merged_section2->date_completed );
		}
		
		// Verify all task statuses were preserved
		$merged_task1 = $merged_plan->get_task( 'corporate_build_track', 'basic_site_setup', 'corporate_quick_setup' );
		$this->assertNotNull( $merged_task1 );
		$this->assertEquals( 'done', $merged_task1->status );
		
		$merged_task2 = $merged_plan->get_task( 'corporate_build_track', 'basic_site_setup', 'corporate_add_content' );
		if ( $merged_task2 ) {
			$this->assertEquals( 'dismissed', $merged_task2->status );
		}
		
		$merged_task3 = $merged_plan->get_task( 'corporate_build_track', 'advanced_corporate_setup', 'corporate_customize' );
		if ( $merged_task3 ) {
			$this->assertEquals( 'new', $merged_task3->status );
		}
	}

	/**
	 * Test merge with language change scenario
	 */
	public function test_merge_with_language_change() {
		// Create a new plan (with updated language content)
		$new_plan = PlanFactory::create_plan( 'blog' );
		
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
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans (simulating language change)
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify user progress was preserved
		$merged_track = $merged_plan->get_track( 'blog_build_track' );
		$this->assertTrue( $merged_track->open );
		
		$merged_section = $merged_plan->get_section( 'blog_build_track', 'basic_blog_setup' );
		$this->assertTrue( $merged_section->open );
		$this->assertEquals( 'completed', $merged_section->status );
		$this->assertEquals( '2024-01-01 12:00:00', $merged_section->date_completed );
		
		$merged_task = $merged_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertEquals( 'done', $merged_task->status );
		
		// Verify that the plan structure is complete (new language content merged in)
		$this->assertGreaterThan( 0, count( $merged_plan->tracks ) );
		$this->assertGreaterThan( 0, count( $merged_track->sections ) );
		$this->assertGreaterThan( 0, count( $merged_section->tasks ) );
	}

	/**
	 * Test merge with empty saved plan
	 */
	public function test_merge_with_empty_saved_plan() {
		// Create a new plan
		$new_plan = PlanFactory::create_plan( 'ecommerce' );
		
		// Create an empty saved plan
		$saved_plan_data = array(
			'id'          => 'store_setup',
			'type'        => 'ecommerce',
			'label'       => 'Store Setup',
			'description' => 'Set up your store',
			'version'     => '1.0.0',
			'tracks'      => array(),
		);
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify the new plan structure is used
		$this->assertEquals( 'store_setup', $merged_plan->id );
		$this->assertEquals( 'ecommerce', $merged_plan->type );
		$this->assertGreaterThan( 0, count( $merged_plan->tracks ) );
		
		// Verify all tracks have default state
		foreach ( $merged_plan->tracks as $track ) {
			$this->assertTrue( $track->open );
		}
	}

	/**
	 * Test merge with corrupted saved plan data
	 */
	public function test_merge_with_corrupted_saved_plan() {
		// Create a new plan
		$new_plan = PlanFactory::create_plan( 'blog' );
		
		// Create a corrupted saved plan (missing required fields)
		$saved_plan_data = array(
			'id'     => 'blog_setup',
			'type'   => 'blog',
			'tracks' => array(
				array(
					'id'       => 'blog_build_track',
					'sections' => array(
						array(
							'id'    => 'basic_blog_setup',
							'tasks' => array(
								array(
									'id'     => 'blog_quick_setup',
									'status' => 'done',
								),
							),
						),
					),
				),
			),
		);
		
		$saved_plan = new Plan( $saved_plan_data );
		
		// Merge the plans
		$merged_plan = PlanRepository::merge_plan_data( $saved_plan, $new_plan );
		
		// Verify the merge still works and preserves what it can
		$this->assertEquals( 'blog_setup', $merged_plan->id );
		$this->assertEquals( 'blog', $merged_plan->type );
		
		// Verify the task status was preserved
		$merged_task = $merged_plan->get_task( 'blog_build_track', 'basic_blog_setup', 'blog_quick_setup' );
		$this->assertEquals( 'done', $merged_task->status );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up options after each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );

		parent::tearDown();
	}
}
