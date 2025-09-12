<?php
/**
 * Tests for PlanLoader class
 *
 * @package WPModuleNextSteps
 * 
 * @phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
 */

use NewfoldLabs\WP\Module\NextSteps\PlanFactory;
use NewfoldLabs\WP\Module\NextSteps\PlanRepository;
use NewfoldLabs\WP\Module\NextSteps\StepsApi;
use NewfoldLabs\WP\Module\NextSteps\Tests\PHPUnit\TestPlanFactory;

/**
 * Class PlanLoaderTest
 *
 * @package WPModuleNextSteps
 */
class PlanFactoryTest extends WP_UnitTestCase {

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean up options before each test
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanRepository::OPTION );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );
	}

	/**
	 * Test load_default_steps when no steps exist
	 */
	public function test_load_default_steps_when_no_steps_exist() {
		// Ensure no steps option exists
		delete_option( PlanRepository::OPTION );

		// No external option setup needed - test should use intelligent detection
		// which defaults to 'blog' in test environment

		PlanFactory::load_default_steps();

		// Verify that steps were loaded and saved to the module's own option
		$steps_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'blog_setup', $steps_data['id'] );
	}

	/**
	 * Test load_default_steps when steps already exist
	 */
	public function test_load_default_steps_when_steps_exist() {
		// Set existing steps data using TestPlan
		$existing_plan = TestPlanFactory::create_minimal_plan();
		update_option( StepsApi::OPTION, $existing_plan->to_array() );

		PlanFactory::load_default_steps();

		// Verify that existing steps were not overwritten
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertEquals( 'test_plan_minimal', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change with valid site type change
	 */
	public function test_on_sitetype_change_valid_change() {
		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'ecommerce' );

		PlanFactory::on_sitetype_change( $old_value, $new_value );

		// Verify steps data was updated
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'store_setup', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change when site type doesn't change
	 */
	public function test_on_sitetype_change_no_change() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'personal' ); // Same as old

		PlanFactory::on_sitetype_change( $old_value, $new_value );

		// Verify no steps were loaded since there was no change
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertFalse( $steps_data, 'No steps should be loaded when site type does not change' );
	}

	/**
	 * Test on_sitetype_change with empty/false old value (first time setup)
	 */
	public function test_on_sitetype_change_first_time_setup() {
		$old_value = false; // This is what WordPress returns for non-existent options
		$new_value = array(
			'site_type'  => 'business',
			'other_data' => 'test',
		);

		PlanFactory::on_sitetype_change( $old_value, $new_value );

		// Verify steps data was updated
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'corporate_setup', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change with invalid new value structure
	 */
	public function test_on_sitetype_change_invalid_new_value() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		$old_value = array( 'site_type' => 'personal' );

		// Test with non-array new value
		$new_value = 'invalid';
		PlanFactory::on_sitetype_change( $old_value, $new_value );

		// Verify no steps were loaded due to invalid input
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertFalse( $steps_data, 'No steps should be loaded for invalid new value' );

		// Test with array missing site_type key
		$new_value = array( 'other_key' => 'value' );
		PlanFactory::on_sitetype_change( $old_value, $new_value );

		// Verify no steps were loaded due to missing site_type
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertFalse( $steps_data, 'No steps should be loaded for missing site_type key' );
	}

	/**
	 * Test on_sitetype_change with invalid site type
	 */
	public function test_on_sitetype_change_invalid_site_type() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		$old_value = array( 'site_type' => 'personal' );
		$new_value = array( 'site_type' => 'invalid_type' );

		PlanFactory::on_sitetype_change( $old_value, $new_value );

		// Verify no steps were loaded for invalid site type
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertFalse( $steps_data, 'No steps should be loaded for invalid site type' );
	}

	/**
	 * Test on_sitetype_change with complex old value structure
	 */
	public function test_on_sitetype_change_complex_old_value() {
		$old_value = array(
			'site_type'       => 'personal',
			'additional_data' => array(
				'nested' => 'value',
			),
		);
		$new_value = array(
			'site_type'       => 'ecommerce',
			'additional_data' => array(
				'nested' => 'new_value',
			),
		);

		PlanFactory::on_sitetype_change( $old_value, $new_value );

		// The module should NOT modify the solution option - it's read-only
		// We only verify that the correct plan was loaded

		// Verify steps were updated
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'store_setup', $steps_data['id'] );
	}

	/**
	 * Test on_sitetype_change handles all valid site types
	 */
	public function test_on_sitetype_change_all_valid_types() {
		$valid_types = array(
			'personal'  => array(
				'solution' => 'blog',
				'plan_id'  => 'blog_setup',
			),
			'business'  => array(
				'solution' => 'corporate',
				'plan_id'  => 'corporate_setup',
			),
			'ecommerce' => array(
				'solution' => 'ecommerce',
				'plan_id'  => 'store_setup',
			),
		);

		$old_site_types = array( 'ecommerce', 'personal', 'business' ); // Rotate old values
		$i              = 0;

		foreach ( $valid_types as $site_type => $expected ) {
			// Clean slate for each test
			delete_option( PlanRepository::OPTION );

			delete_option( PlanRepository::OPTION );

			// Use a different old value to ensure a change is detected
			$old_value = array( 'site_type' => $old_site_types[ $i ] );
			$new_value = array( 'site_type' => $site_type );
			++$i;

			PlanFactory::on_sitetype_change( $old_value, $new_value );

			// Verify correct plan was loaded
			$steps_data = get_option( StepsApi::OPTION );
			$this->assertIsArray( $steps_data, "Steps data should be an array for site_type: $site_type, got: " . var_export( $steps_data, true ) );
			$this->assertArrayHasKey( 'id', $steps_data, "Steps data should have 'id' key" );
			$this->assertEquals( $expected['plan_id'], $steps_data['id'] );
		}
	}

	/**
	 * Test on_woocommerce_activation switches from blog to ecommerce
	 */
	public function test_on_woocommerce_activation() {
		// Clean slate
		delete_option( PlanRepository::OPTION );
		delete_option( PlanRepository::OPTION );

		// Set up initial blog steps
		$blog_plan = PlanRepository::switch_plan( 'blog' );
		$this->assertNotFalse( $blog_plan );
		StepsApi::set_data( $blog_plan->to_array() );

		// Verify initial state is blog
		$initial_steps = get_option( StepsApi::OPTION );
		$this->assertIsArray( $initial_steps );
		$this->assertEquals( 'blog_setup', $initial_steps['id'] );

		// Simulate WooCommerce activation
		PlanFactory::on_woocommerce_activation( 'woocommerce/woocommerce.php', false );

		// Verify steps switched to ecommerce
		$updated_steps = get_option( StepsApi::OPTION );
		$this->assertIsArray( $updated_steps );
		$this->assertEquals( 'store_setup', $updated_steps['id'] );
		$this->assertNotEquals( $initial_steps, $updated_steps );
	}

	/**
	 * Test on_woocommerce_activation ignores other plugins
	 */
	public function test_on_woocommerce_activation_ignores_other_plugins() {
		// Clean slate
		delete_option( PlanRepository::OPTION );
		delete_option( PlanRepository::OPTION );

		// Set up initial blog steps
		$blog_plan = PlanRepository::switch_plan( 'blog' );
		$this->assertNotFalse( $blog_plan );
		StepsApi::set_data( $blog_plan->to_array() );

		// Get initial steps state
		$initial_steps = get_option( StepsApi::OPTION );
		$this->assertEquals( 'blog_setup', $initial_steps['id'] );

		// Simulate activation of a different plugin
		PlanFactory::on_woocommerce_activation( 'some-other-plugin/plugin.php', false );

		// Verify steps did NOT change
		$unchanged_steps = get_option( StepsApi::OPTION );
		$this->assertEquals( $initial_steps, $unchanged_steps );
		$this->assertEquals( 'blog_setup', $unchanged_steps['id'] );
	}

	/**
	 * Test that PlanLoader constructor sets up hooks correctly
	 */
	public function test_constructor_sets_up_hooks() {
		// Instantiate PlanFactory to trigger constructor and hook registration
		new PlanFactory();

		// Verify init hook is added
		$this->assertEquals( 1, has_action( 'init', array( 'NewfoldLabs\WP\Module\NextSteps\PlanFactory', 'load_default_steps' ) ) );

		// Verify option update hook is added
		$this->assertEquals( 10, has_action( 'update_option_' . PlanFactory::ONBOARDING_SITE_INFO_OPTION, array( 'NewfoldLabs\WP\Module\NextSteps\PlanFactory', 'on_sitetype_change' ) ) );
	}

	/**
	 * Test complete workflow: onboarding sets site type -> plan loads
	 */
	public function test_complete_onboarding_workflow() {
		// Simulate onboarding module setting site info for the first time

		// Step 1: No existing data (fresh install)
		$this->assertFalse( get_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION ) );
		$this->assertFalse( get_option( StepsApi::OPTION ) );

		// Step 2: Onboarding module sets site info
		$site_info = array(
			'site_type'     => 'business',
			'business_name' => 'Test Corp',
			'description'   => 'A test corporate site',
		);

		// Simulate the option update that would trigger our hook
		update_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION, $site_info );

		// Manually trigger the hook (since WordPress hooks don't fire in unit tests)
		PlanFactory::on_sitetype_change( false, $site_info );

		// Verify that the correct plan was loaded
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
		update_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION, $initial_site_info );
		PlanFactory::on_sitetype_change( false, $initial_site_info );

		// Verify initial setup loaded blog steps
		$steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertEquals( 'blog_setup', $steps_data['id'] );

		// User changes their mind and switches to ecommerce
		$updated_site_info = array(
			'site_type'     => 'ecommerce',
			'business_name' => 'My Store',
		);

		PlanFactory::on_sitetype_change( $initial_site_info, $updated_site_info );

		$updated_steps_data = get_option( StepsApi::OPTION );
		$this->assertIsArray( $updated_steps_data );
		$this->assertEquals( 'store_setup', $updated_steps_data['id'] );
		$this->assertNotEquals( $steps_data, $updated_steps_data );
	}

	/**
	 * Test site type detection for existing sites without onboarding data
	 */
	public function test_detect_site_type_defaults_to_blog() {
		// Clean slate - no special setup needed

		// Mock a simple site with no special indicators
		$detected_type = PlanFactory::detect_site_type();

		$this->assertEquals( 'blog', $detected_type );
	}

	/**
	 * Test load_default_steps with backfill for existing sites
	 */
	public function test_load_default_steps_backfills_solution() {
		// Clean slate - simulate existing site without onboarding or solution data
		delete_option( PlanRepository::OPTION );

		// Test the full flow - this should load default steps based on site detection
		PlanFactory::load_default_steps();

		// Verify steps were loaded using intelligent detection
		$steps_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $steps_data );
		$this->assertArrayHasKey( 'id', $steps_data );
		// Should default to blog in test environment (no ecommerce/corporate indicators)
		$this->assertEquals( 'blog_setup', $steps_data['id'] );
	}

	/**
	 * Test load_default_steps respects existing solution from transient
	 */
	public function test_load_default_steps_respects_existing_solution() {
		// Clean slate
		delete_option( PlanRepository::OPTION );

		// Set an existing solution via transient (primary method)
		$solutions_data = array( 'solution' => 'WP_SOLUTION_COMMERCE' );
		set_transient( PlanFactory::SOLUTIONS_TRANSIENT, $solutions_data );

		PlanFactory::load_default_steps();

		// Verify correct plan was loaded
		$steps_data = get_option( PlanRepository::OPTION );
		$this->assertIsArray( $steps_data );
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
		delete_option( PlanRepository::OPTION );
		delete_transient( PlanFactory::SOLUTIONS_TRANSIENT );
		delete_option( PlanRepository::OPTION );
		delete_option( PlanFactory::ONBOARDING_SITE_INFO_OPTION );

		parent::tearDown();
	}
}
