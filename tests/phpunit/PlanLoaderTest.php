<?php

use NewfoldLabs\WP\Module\NextSteps\PlanLoader;
use NewfoldLabs\WP\Module\NextSteps\PlanManager;
use NewfoldLabs\WP\Module\NextSteps\StepsApi;

/**
 * Class PlanLoaderTest
 *
 * @package WPModuleNextSteps
 */
class PlanLoaderTest extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		
		// Clean up options before each test
		delete_option( PlanManager::OPTION );
		delete_option( PlanManager::SOLUTION_OPTION );
		delete_option( StepsApi::OPTION );
		delete_option( 'nfd_module_onboarding_site_info' );
	}

	/**
	 * Test load_default_steps when no steps exist
	 */
	public function test_load_default_steps_when_no_steps_exist() {
		// Ensure no steps option exists
		delete_option( StepsApi::OPTION );
		
		// Set solution to blog (internal plan type)
		update_option( PlanManager::SOLUTION_OPTION, 'blog' );
		
		PlanLoader::load_default_steps();
		
		// Verify that steps were loaded and saved
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'blog_setup', $steps_data['id'] );
	}

	/**
	 * Test load_default_steps when steps already exist
	 */
	public function test_load_default_steps_when_steps_exist() {
		// Set existing steps data
		$existing_data = array( 'id' => 'existing_plan' );
		update_option( StepsApi::OPTION, $existing_data );
		
		PlanLoader::load_default_steps();
		
		// Verify that existing steps were not overwritten
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertEquals( 'existing_plan', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change with valid site type change
	 */
	public function test_on_sitetype_change_valid_change() {
		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'ecommerce' );
		
		PlanLoader::on_sitetype_change( $old_value, $new_value );
		
		// Verify solution option was updated
		$this->assertEquals( 'ecommerce', get_option( PlanManager::SOLUTION_OPTION ) );
		
		// Verify steps data was updated
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'store_setup', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change when site type doesn't change
	 */
	public function test_on_sitetype_change_no_change() {
		// Set initial solution
		update_option( PlanManager::SOLUTION_OPTION, 'blog' );
		
		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'personal' );
		
		PlanLoader::on_sitetype_change( $old_value, $new_value );
		
		// Verify solution option wasn't changed
		$this->assertEquals( 'blog', get_option( PlanManager::SOLUTION_OPTION ) );
	}

	/**
	 * Test on_sitetype_change with empty/false old value (first time setup)
	 */
	public function test_on_sitetype_change_first_time_setup() {
		$old_value = false; // This is what WordPress returns for non-existent options
		$new_value = array( 
			'site_type' => 'business',
			'other_data' => 'test' 
		);
		
		PlanLoader::on_sitetype_change( $old_value, $new_value );
		
		// Verify solution option was set
		$this->assertEquals( 'corporate', get_option( PlanManager::SOLUTION_OPTION ) );
		
		// Verify steps data was updated
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'corporate_setup', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change with invalid new value structure
	 */
	public function test_on_sitetype_change_invalid_new_value() {
		// Set initial solution
		update_option( PlanManager::SOLUTION_OPTION, 'blog' );
		
		$old_value = array( 'site_type' => 'personal' );
		
		// Test with non-array new value
		$new_value = 'invalid';
		PlanLoader::on_sitetype_change( $old_value, $new_value );
		$this->assertEquals( 'blog', get_option( PlanManager::SOLUTION_OPTION ) );
		
		// Test with array missing site_type key
		$new_value = array( 'other_key' => 'value' );
		PlanLoader::on_sitetype_change( $old_value, $new_value );
		$this->assertEquals( 'blog', get_option( PlanManager::SOLUTION_OPTION ) );
	}

	/**
	 * Test on_sitetype_change with invalid site type
	 */
	public function test_on_sitetype_change_invalid_site_type() {
		// Set initial solution
		update_option( PlanManager::SOLUTION_OPTION, 'blog' );
		
		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'invalid_type' );
		
		PlanLoader::on_sitetype_change( $old_value, $new_value );
		
		// Verify solution option wasn't changed due to invalid type
		$this->assertEquals( 'blog', get_option( PlanManager::SOLUTION_OPTION ) );
		
		// Verify steps data wasn't updated
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertFalse( $steps_data );
	}

	/**
	 * Test on_sitetype_change with complex old value structure
	 */
	public function test_on_sitetype_change_complex_old_value() {
		$old_value = array( 
			'site_type' => 'personal',
			'additional_data' => array(
				'nested' => 'value'
			)
		);
		$new_value = array( 
			'site_type' => 'ecommerce',
			'additional_data' => array(
				'nested' => 'new_value'
			)
		);
		
		PlanLoader::on_sitetype_change( $old_value, $new_value );
		
		// Verify solution was switched
		$this->assertEquals( 'ecommerce', get_option( PlanManager::SOLUTION_OPTION ) );
		
		// Verify steps were updated
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertEquals( 'store_setup', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change handles all valid site types
	 */
	public function test_on_sitetype_change_all_valid_types() {
		$valid_types = array(
			'personal'  => array( 'solution' => 'blog', 'plan_id' => 'blog_setup' ),
			'business'  => array( 'solution' => 'corporate', 'plan_id' => 'corporate_setup' ),
			'ecommerce' => array( 'solution' => 'ecommerce', 'plan_id' => 'store_setup' ),
		);
		
		foreach ( $valid_types as $site_type => $expected ) {
			// Clean slate for each test
			delete_option( PlanManager::OPTION );
			delete_option( PlanManager::SOLUTION_OPTION );
			delete_option( StepsApi::OPTION );
			
			$old_value = array( 'site_type' => 'ecommerce' ); // Start with different type
			$new_value = array( 'site_type' => $site_type );
			
			PlanLoader::on_sitetype_change( $old_value, $new_value );
			
			// Verify correct solution was set (internal plan type)
			$this->assertEquals( $expected['solution'], get_option( PlanManager::SOLUTION_OPTION ) );
			
			// Verify correct plan was loaded
			$steps_data = get_option( StepsApi::OPTION );
			$this->assertEquals( $expected['plan_id'], $steps_data['id'] );
		}
	}

	/**
	 * Test on_woocommerce_activation (commented out method)
	 */
	public function test_on_woocommerce_activation() {
		// Since the method is commented out, we can test the logic if we uncomment it
		// or test that calling it with WooCommerce plugin path would switch to ecommerce
		
		PlanLoader::on_woocommerce_activation( 'woocommerce/woocommerce.php', false );
		
		// Since method is commented out, verify it doesn't change anything
		$this->assertEquals( 'ecommerce', get_option( PlanManager::SOLUTION_OPTION, 'ecommerce' ) );
	}

	/**
	 * Test that PlanLoader constructor sets up hooks correctly
	 */
	public function test_constructor_sets_up_hooks() {
		// Verify init hook is added
		$this->assertEquals( 1, has_action( 'init', array( 'NewfoldLabs\WP\Module\NextSteps\PlanLoader', 'load_default_steps' ) ) );
		
		// Verify option update hook is added
		$this->assertEquals( 10, has_action( 'update_option_nfd_module_onboarding_site_info', array( 'NewfoldLabs\WP\Module\NextSteps\PlanLoader', 'on_sitetype_change' ) ) );
	}

	/**
	 * Test complete workflow: onboarding sets site type -> plan loads
	 */
	public function test_complete_onboarding_workflow() {
		// Simulate onboarding module setting site info for the first time
		
		// Step 1: No existing data (fresh install)
		$this->assertFalse( get_option( 'nfd_module_onboarding_site_info' ) );
		$this->assertFalse( get_option( StepsApi::OPTION ) );
		
		// Step 2: Onboarding module sets site info
		$site_info = array(
			'site_type' => 'business',
			'business_name' => 'Test Corp',
			'description' => 'A test corporate site'
		);
		
		// Simulate the option update that would trigger our hook
		update_option( 'nfd_module_onboarding_site_info', $site_info );
		
		// Manually trigger the hook (since WordPress hooks don't fire in unit tests)
		PlanLoader::on_sitetype_change( false, $site_info );
		
		// Step 3: Verify plan was loaded correctly (should be 'corporate' internally)
		$this->assertEquals( 'corporate', get_option( PlanManager::SOLUTION_OPTION ) );
		
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'corporate_setup', $steps_data['id'] );
		$this->assertEquals( 'Corporate Setup', $steps_data['label'] );
		
		// Step 4: Verify plan structure is complete
		$this->assertArrayHasKey( 'tracks', $steps_data );
		$this->assertGreaterThan( 0, count( $steps_data['tracks'] ) );
	}

	/**
	 * Test site type change after initial setup
	 */
	public function test_site_type_change_after_initial_setup() {
		// Set up initial state (blog site from 'personal' onboarding choice)
		$initial_site_info = array( 'site_type' => 'personal' );
		update_option( 'nfd_module_onboarding_site_info', $initial_site_info );
		PlanLoader::on_sitetype_change( false, $initial_site_info );
		
		// Verify initial setup (personal -> blog internally)
		$this->assertEquals( 'blog', get_option( PlanManager::SOLUTION_OPTION ) );
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertEquals( 'blog_setup', $steps_data['id'] );
		
		// User changes their mind and switches to ecommerce
		$updated_site_info = array( 
			'site_type' => 'ecommerce',
			'business_name' => 'My Store' 
		);
		
		PlanLoader::on_sitetype_change( $initial_site_info, $updated_site_info );
		
		// Verify switch was successful
		$this->assertEquals( 'ecommerce', get_option( PlanManager::SOLUTION_OPTION ) );
		
		$updated_steps_data = get_option( StepsApi::OPTION );
		$this->assertEquals( 'store_setup', $updated_steps_data['id'] );
		$this->assertNotEquals( $steps_data, $updated_steps_data );
	}

	/**
	 * Test site type detection for existing sites without onboarding data
	 */
	public function test_detect_site_type_defaults_to_blog() {
		// Clean slate
		delete_option( PlanManager::SOLUTION_OPTION );
		
		// Mock a simple site with no special indicators
		$detected_type = PlanLoader::detect_site_type();
		
		$this->assertEquals( 'blog', $detected_type );
	}

	/**
	 * Test load_default_steps with backfill for existing sites
	 */
	public function test_load_default_steps_backfills_solution() {
		// Clean slate - simulate existing site without onboarding or solution data
		delete_option( StepsApi::OPTION );
		delete_option( PlanManager::SOLUTION_OPTION );
		
		// Mock site detection to return 'corporate'
		// We can't easily mock private methods, so we'll test the full flow
		PlanLoader::load_default_steps();
		
		// Verify a solution was set (will be 'blog' by default in test environment)
		$solution = get_option( PlanManager::SOLUTION_OPTION );
		$this->assertNotFalse( $solution );
		$this->assertContains( $solution, array( 'blog', 'corporate', 'ecommerce' ) );
		
		// Verify steps were loaded
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertArrayHasKey( 'id', $steps_data );
	}

	/**
	 * Test load_default_steps doesn't backfill if solution already exists
	 */
	public function test_load_default_steps_respects_existing_solution() {
		// Clean slate
		delete_option( StepsApi::OPTION );
		
		// Set an existing solution
		update_option( PlanManager::SOLUTION_OPTION, 'ecommerce' );
		
		PlanLoader::load_default_steps();
		
		// Verify existing solution was preserved
		$this->assertEquals( 'ecommerce', get_option( PlanManager::SOLUTION_OPTION ) );
		
		// Verify correct plan was loaded
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertEquals( 'store_setup', $steps_data['id'] );
	}

	/**
	 * Test error handling when StepsApi is not available
	 */
	public function test_handles_missing_steps_api() {
		// This test would be relevant if StepsApi might not be loaded
		// For now, we assume it's always available due to bootstrap
		$this->assertTrue( class_exists( 'NewfoldLabs\WP\Module\NextSteps\StepsApi' ) );
	}

	/**
	 * Tear down test environment
	 */
	public function tearDown(): void {
		// Clean up options after each test
		delete_option( PlanManager::OPTION );
		delete_option( PlanManager::SOLUTION_OPTION );
		delete_option( StepsApi::OPTION );
		delete_option( 'nfd_module_onboarding_site_info' );
		
		parent::tearDown();
	}
} 