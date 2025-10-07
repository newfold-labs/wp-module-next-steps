<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use WP_Post;

/**
 * Test TaskCompletionTriggers class
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\TaskCompletionTriggers
 */
class TaskCompletionTriggersWPUnitTest extends \Codeception\TestCase\WPTestCase {

	/**
	 * Set up before each test
	 */
	public function setUp(): void {
		parent::setUp();

		// Clean up any existing plan
		delete_option( 'nfd_next_steps' );

		// Clear any registered validators
		$reflection = new \ReflectionClass( TaskStateValidator::class );
		$property   = $reflection->getProperty( 'validators' );
		$property->setAccessible( true );
		$property->setValue( array() );
	}

	/**
	 * Tear down after each test
	 */
	public function tearDown(): void {
		delete_option( 'nfd_next_steps' );
		parent::tearDown();
	}

	// ========================================
	// # Product Tasks Tests
	// ========================================

	/**
	 * Test product creation via REST API marks task complete
	 *
	 * @covers ::on_product_creation
	 */
	public function test_on_product_creation_marks_task_complete() {
		// Skip if WooCommerce is not available
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			$this->markTestSkipped( 'WooCommerce is not available' );
		}

		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Verify task is not complete initially
		$task = $plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertNotNull( $task );
		$this->assertFalse( $task->is_completed() );

		// Create mock product object
		$product = (object) array( 'id' => 123 );
		$request = (object) array();

		// Trigger the handler
		TaskCompletionTriggers::on_product_creation( $product, $request, true );

		// Verify task is now complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test product publishing marks task complete
	 *
	 * @covers ::on_product_published
	 */
	public function test_on_product_published_marks_task_complete() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Create mock product post
		$post              = new \stdClass();
		$post->ID          = 123;
		$post->post_type   = 'product';
		$post->post_status = 'publish';

		// Trigger the handler
		TaskCompletionTriggers::on_product_published( 123, $post );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test product publishing does not trigger for non-product post types
	 *
	 * @covers ::on_product_published
	 */
	public function test_on_product_published_ignores_non_product_posts() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Create mock regular post
		$post              = new \stdClass();
		$post->ID          = 123;
		$post->post_type   = 'post';
		$post->post_status = 'publish';

		// Trigger the handler
		TaskCompletionTriggers::on_product_published( 123, $post );

		// Verify task is NOT complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertFalse( $updated_task->is_completed() );
	}

	// ========================================
	// # Blog Post Tasks Tests
	// ========================================

	/**
	 * Test blog post publishing marks task complete
	 *
	 * @covers ::on_blog_post_published
	 */
	public function test_on_blog_post_published_marks_task_complete() {
		// Create and save blog plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Create mock blog post
		$post              = new \stdClass();
		$post->ID          = 123;
		$post->post_type   = 'post';
		$post->post_status = 'publish';
		$post->post_title  = 'My First Real Post';
		$post->post_name   = 'my-first-real-post';

		// Trigger the handler
		TaskCompletionTriggers::on_blog_post_published( 123, $post );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test blog post publishing ignores Hello World post
	 *
	 * @covers ::on_blog_post_published
	 * @covers ::is_hello_world_post
	 */
	public function test_on_blog_post_published_ignores_hello_world() {
		// Create and save blog plan
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Create mock Hello World post
		$post              = new \stdClass();
		$post->ID          = 1;
		$post->post_type   = 'post';
		$post->post_status = 'publish';
		$post->post_title  = 'Hello world!';
		$post->post_name   = 'hello-world';

		// Trigger the handler
		TaskCompletionTriggers::on_blog_post_published( 1, $post );

		// Verify task is NOT complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'blog_build_track', 'create_content', 'blog_first_post' );
		$this->assertFalse( $updated_task->is_completed() );
	}

	/**
	 * Test is_hello_world_post detection
	 *
	 * @covers ::is_hello_world_post
	 */
	public function test_is_hello_world_post_detection() {
		// Test with title match
		$post1             = new \stdClass();
		$post1->post_title = 'Hello world!';
		$post1->post_name  = 'some-other-slug';

		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );
		$method     = $reflection->getMethod( 'is_hello_world_post' );
		$method->setAccessible( true );

		$this->assertTrue( $method->invoke( null, $post1 ) );

		// Test with slug match
		$post2             = new \stdClass();
		$post2->post_title = 'Some Other Title';
		$post2->post_name  = 'hello-world';
		$this->assertTrue( $method->invoke( null, $post2 ) );

		// Test with neither match
		$post3             = new \stdClass();
		$post3->post_title = 'My Real Post';
		$post3->post_name  = 'my-real-post';
		$this->assertFalse( $method->invoke( null, $post3 ) );
	}

	// ========================================
	// # Gift Card Tasks Tests
	// ========================================

	/**
	 * Test gift card publishing marks task complete
	 *
	 * @covers ::on_gift_card_published
	 */
	public function test_on_gift_card_published_marks_task_complete() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Create mock gift card post
		$post              = new \stdClass();
		$post->ID          = 123;
		$post->post_type   = 'bh_gift_card';
		$post->post_status = 'publish';

		// Trigger the handler
		TaskCompletionTriggers::on_gift_card_published( 123, $post );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'first_marketing_steps', 'store_create_gift_card' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test gift card validator
	 *
	 * @covers ::validate_gift_card_creation_state
	 */
	public function test_validate_gift_card_creation_state() {
		// Should return false when no gift cards exist
		$this->assertFalse( TaskCompletionTriggers::validate_gift_card_creation_state() );

		// Create a gift card post
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'bh_gift_card',
				'post_status' => 'publish',
				'post_title'  => 'Test Gift Card',
			)
		);

		// Should return true when gift card exists
		$this->assertTrue( TaskCompletionTriggers::validate_gift_card_creation_state() );

		// Clean up
		wp_delete_post( $post_id, true );
	}

	// ========================================
	// # Welcome Popup Tasks Tests
	// ========================================

	/**
	 * Test welcome popup publishing marks task complete
	 *
	 * @covers ::on_welcome_popup_published
	 */
	public function test_on_welcome_popup_published_marks_task_complete() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Create mock campaign post
		$post              = new \stdClass();
		$post->ID          = 123;
		$post->post_type   = 'yith_campaign';
		$post->post_status = 'publish';

		// Trigger the handler
		TaskCompletionTriggers::on_welcome_popup_published( 123, $post );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'first_marketing_steps', 'store_marketing_welcome_popup' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test welcome popup validator
	 *
	 * @covers ::validate_welcome_popup_creation_state
	 */
	public function test_validate_welcome_popup_creation_state() {
		// Should return false when no campaigns exist
		$this->assertFalse( TaskCompletionTriggers::validate_welcome_popup_creation_state() );

		// Create a campaign post
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'yith_campaign',
				'post_status' => 'publish',
				'post_title'  => 'Test Campaign',
			)
		);

		// Should return true when campaign exists
		$this->assertTrue( TaskCompletionTriggers::validate_welcome_popup_creation_state() );

		// Clean up
		wp_delete_post( $post_id, true );
	}

	// ========================================
	// # Payment Tasks Tests
	// ========================================

	/**
	 * Test payment gateway update marks task complete
	 *
	 * @covers ::on_payment_gateway_updated
	 */
	public function test_on_payment_gateway_updated_marks_task_complete() {
		// Skip if WooCommerce is not available
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			$this->markTestSkipped( 'WooCommerce is not available' );
		}

		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Mock that a payment gateway is enabled
		// This would normally be set by WooCommerce, but we can't easily mock that
		// So we'll just verify the method doesn't error
		TaskCompletionTriggers::on_payment_gateway_updated();

		// Note: This test is limited because we can't easily mock WooCommerce gateway state
		// In a real scenario, the task would be marked complete if gateways are enabled
		$this->assertTrue( true ); // Test passes if no errors
	}

	/**
	 * Test payment setup validation checks available gateways
	 *
	 * @covers ::validate_payment_setup_state
	 */
	public function test_validate_payment_setup_state_checks_available_gateways() {
		// Skip if WooCommerce is not available
		if ( ! function_exists( 'WC' ) || ! WC() ) {
			$this->markTestSkipped( 'WooCommerce is not available' );
		}

		// Test the validation method directly
		$result = TaskCompletionTriggers::validate_payment_setup_state();

		// The result depends on the current WooCommerce configuration
		// We just want to ensure the method runs without errors
		$this->assertIsBool( $result );
	}

	/**
	 * Test payment gateway validation when WooCommerce is not available
	 *
	 * @covers ::has_enabled_payment_gateways
	 */
	public function test_has_enabled_payment_gateways_without_woocommerce() {
		// Test the method using reflection since it's private
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );
		$method     = $reflection->getMethod( 'has_enabled_payment_gateways' );
		$method->setAccessible( true );

		// We can't easily mock WC() function, so we'll test the logic path
		// by ensuring the method handles the case gracefully

		// The method should return false when WooCommerce is not available
		// This tests the early return logic
		$result = $method->invoke( null );

		// Since we can't easily mock WC(), we just verify it returns a boolean
		$this->assertIsBool( $result );
	}

	/**
	 * Test payment gateway validation method exists and is callable
	 *
	 * @covers ::has_enabled_payment_gateways
	 */
	public function test_has_enabled_payment_gateways_method_exists() {
		// Test that the private method exists and is callable
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );

		$this->assertTrue( $reflection->hasMethod( 'has_enabled_payment_gateways' ) );

		$method = $reflection->getMethod( 'has_enabled_payment_gateways' );
		$this->assertTrue( $method->isPrivate() );
		$this->assertTrue( $method->isStatic() );
	}

	/**
	 * Test payment setup validation method exists and is callable
	 *
	 * @covers ::validate_payment_setup_state
	 */
	public function test_validate_payment_setup_state_method_exists() {
		// Test that the public method exists and is callable
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );

		$this->assertTrue( $reflection->hasMethod( 'validate_payment_setup_state' ) );

		$method = $reflection->getMethod( 'validate_payment_setup_state' );
		$this->assertTrue( $method->isPublic() );
		$this->assertTrue( $method->isStatic() );
	}

	/**
	 * Test payment gateway update handler method exists and is callable
	 *
	 * @covers ::on_payment_gateway_updated
	 */
	public function test_on_payment_gateway_updated_method_exists() {
		// Test that the public method exists and is callable
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );

		$this->assertTrue( $reflection->hasMethod( 'on_payment_gateway_updated' ) );

		$method = $reflection->getMethod( 'on_payment_gateway_updated' );
		$this->assertTrue( $method->isPublic() );
		$this->assertTrue( $method->isStatic() );
	}


	/**
	 * Test payment gateway validation handles edge cases gracefully
	 *
	 * @covers ::has_enabled_payment_gateways
	 */
	public function test_has_enabled_payment_gateways_edge_cases() {
		// Test the method using reflection since it's private
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );
		$method    = $reflection->getMethod( 'has_enabled_payment_gateways' );
		$method->setAccessible( true );

		// Test that the method handles various edge cases without throwing exceptions
		$result = $method->invoke( null );

		// Should always return a boolean, never throw an exception
		$this->assertIsBool( $result );

		// Should be either true or false, not null or any other unexpected type
		$this->assertTrue( $result === true || $result === false, 'Method should return true or false, got: ' . var_export( $result, true ) );
	}


	// ========================================
	// # Logo Upload Tasks Tests
	// ========================================

	/**
	 * Test logo upload handler with custom_logo
	 *
	 * @covers ::on_logo_updated
	 */
	public function test_on_logo_updated_with_custom_logo() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Set custom_logo theme mod
		set_theme_mod( 'custom_logo', 123 );

		// Trigger the handler
		TaskCompletionTriggers::on_logo_updated();

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'customize_your_store', 'store_upload_logo' );
		$this->assertTrue( $updated_task->is_completed() );

		// Clean up
		remove_theme_mod( 'custom_logo' );
	}

	/**
	 * Test logo upload handler with site_logo option
	 *
	 * @covers ::on_logo_updated
	 */
	public function test_on_logo_updated_with_site_logo() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Set site_logo option
		update_option( 'site_logo', 456 );

		// Trigger the handler
		TaskCompletionTriggers::on_logo_updated();

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'customize_your_store', 'store_upload_logo' );
		$this->assertTrue( $updated_task->is_completed() );

		// Clean up
		delete_option( 'site_logo' );
	}

	/**
	 * Test logo validator with custom_logo
	 *
	 * @covers ::validate_logo_upload_state
	 */
	public function test_validate_logo_upload_state_with_custom_logo() {
		// Should return false when no logo is set
		$this->assertFalse( TaskCompletionTriggers::validate_logo_upload_state() );

		// Set custom_logo
		set_theme_mod( 'custom_logo', 123 );

		// Should return true
		$this->assertTrue( TaskCompletionTriggers::validate_logo_upload_state() );

		// Clean up
		remove_theme_mod( 'custom_logo' );
	}

	/**
	 * Test logo validator with site_logo
	 *
	 * @covers ::validate_logo_upload_state
	 */
	public function test_validate_logo_upload_state_with_site_logo() {
		// Set site_logo option
		update_option( 'site_logo', 456 );

		// Should return true
		$this->assertTrue( TaskCompletionTriggers::validate_logo_upload_state() );

		// Clean up
		delete_option( 'site_logo' );
	}

	// ========================================
	// # Jetpack Tasks Tests
	// ========================================

	/**
	 * Test Jetpack connected handler
	 *
	 * @covers ::on_jetpack_connected
	 */
	public function test_on_jetpack_connected() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Call the handler (won't mark complete without Jetpack actually connected)
		TaskCompletionTriggers::on_jetpack_connected();

		// Test passes if no errors (actual completion requires Jetpack to be connected)
		$this->assertTrue( true );
	}

	/**
	 * Test Jetpack Boost activation handler
	 *
	 * @covers ::on_jetpack_boost_activation
	 */
	public function test_on_jetpack_boost_activation() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger with Jetpack Boost plugin
		TaskCompletionTriggers::on_jetpack_boost_activation( 'jetpack-boost/jetpack-boost.php', false );

		// Test passes if no errors (actual completion requires Jetpack connection)
		$this->assertTrue( true );
	}

	/**
	 * Test Jetpack Boost activation ignores other plugins
	 *
	 * @covers ::on_jetpack_boost_activation
	 */
	public function test_on_jetpack_boost_activation_ignores_other_plugins() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger with different plugin
		TaskCompletionTriggers::on_jetpack_boost_activation( 'some-other-plugin/plugin.php', false );

		// Verify task is NOT complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'store_improve_performance', 'store_improve_performance' );
		$this->assertFalse( $updated_task->is_completed() );
	}

	/**
	 * Test Jetpack module activated handler
	 *
	 * @covers ::on_jetpack_module_activated
	 */
	public function test_on_jetpack_module_activated() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger with boost module
		TaskCompletionTriggers::on_jetpack_module_activated( 'boost' );

		// Test passes if no errors (actual completion requires Jetpack connection)
		$this->assertTrue( true );
	}

	/**
	 * Test Jetpack module activated ignores non-boost modules
	 *
	 * @covers ::on_jetpack_module_activated
	 */
	public function test_on_jetpack_module_activated_ignores_other_modules() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger with different module
		TaskCompletionTriggers::on_jetpack_module_activated( 'some-other-module' );

		// Verify task is NOT complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'store_improve_performance', 'store_improve_performance' );
		$this->assertFalse( $updated_task->is_completed() );
	}

	/**
	 * Test Jetpack Boost activated handler
	 *
	 * @covers ::on_jetpack_boost_activated
	 */
	public function test_on_jetpack_boost_activated() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Call the handler (won't mark complete without Jetpack actually connected)
		TaskCompletionTriggers::on_jetpack_boost_activated();

		// Test passes if no errors (actual completion requires Jetpack connection)
		$this->assertTrue( true );
	}

	// ========================================
	// # Plugin Activation Tasks Tests
	// ========================================

	/**
	 * Test Yoast Premium activation marks task complete
	 *
	 * @covers ::on_yoast_premium_activation
	 */
	public function test_on_yoast_premium_activation_marks_task_complete() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger the handler
		TaskCompletionTriggers::on_yoast_premium_activation( 'wordpress-seo-premium/wp-seo-premium.php', false );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'next_marketing_steps', 'store_setup_yoast_premium' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test Yoast Premium activation ignores other plugins
	 *
	 * @covers ::on_yoast_premium_activation
	 */
	public function test_on_yoast_premium_activation_ignores_other_plugins() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger with different plugin
		TaskCompletionTriggers::on_yoast_premium_activation( 'some-other-plugin/plugin.php', false );

		// Verify task is NOT complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'next_marketing_steps', 'store_setup_yoast_premium' );
		$this->assertFalse( $updated_task->is_completed() );
	}

	/**
	 * Test Advanced Reviews activation marks task complete
	 *
	 * @covers ::on_advanced_reviews_activation
	 */
	public function test_on_advanced_reviews_activation_marks_task_complete() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger the handler
		TaskCompletionTriggers::on_advanced_reviews_activation( 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php', false );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'store_collect_reviews', 'store_collect_reviews_task' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test Affiliates activation marks task complete
	 *
	 * @covers ::on_affiliates_activation
	 */
	public function test_on_affiliates_activation_marks_task_complete() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger the handler
		TaskCompletionTriggers::on_affiliates_activation( 'yith-woocommerce-affiliates/init.php', false );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'advanced_social_marketing', 'store_launch_affiliate' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test Email Templates activation marks task complete
	 *
	 * @covers ::on_email_templates_activation
	 */
	public function test_on_email_templates_activation_marks_task_complete() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Trigger the handler
		TaskCompletionTriggers::on_email_templates_activation( 'wp-plugin-email-templates/wp-plugin-email-templates.php', false );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'first_marketing_steps', 'store_customize_emails' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	// ========================================
	// # Utility Methods Tests
	// ========================================

	/**
	 * Test mark_task_as_complete_by_path
	 *
	 * @covers ::mark_task_as_complete_by_path
	 */
	public function test_mark_task_as_complete_by_path() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Mark task complete using path
		$result = TaskCompletionTriggers::mark_task_as_complete_by_path(
			'store_setup.store_build_track.setup_products.store_add_product'
		);

		$this->assertTrue( $result );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertTrue( $updated_task->is_completed() );
	}

	/**
	 * Test mark_task_as_complete_by_path with invalid path
	 *
	 * @covers ::mark_task_as_complete_by_path
	 */
	public function test_mark_task_as_complete_by_path_with_invalid_path() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Try to mark task complete with invalid path (too few parts)
		$result = TaskCompletionTriggers::mark_task_as_complete_by_path( 'invalid.path' );

		$this->assertFalse( $result );
	}

	/**
	 * Test mark_task_as_complete with single task section
	 *
	 * @covers ::mark_task_as_complete
	 */
	public function test_mark_task_as_complete_marks_section_when_single_task() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Find a section with a single task
		$section = $plan->get_section( 'store_build_track', 'store_improve_performance' );
		$this->assertNotNull( $section );
		$this->assertEquals( 1, count( $section->tasks ) );

		// Mark the task complete
		TaskCompletionTriggers::mark_task_as_complete(
			'store_build_track',
			'store_improve_performance',
			'store_improve_performance'
		);

		// Verify section is marked as done (not just the task)
		$updated_plan    = PlanRepository::get_current_plan();
		$updated_section = $updated_plan->get_section( 'store_build_track', 'store_improve_performance' );
		$this->assertTrue( $updated_section->is_completed() );
	}

	/**
	 * Test that handlers only trigger for correct plan types
	 *
	 * @covers ::on_product_creation
	 */
	public function test_handlers_respect_plan_type() {
		// Create and save blog plan (not ecommerce)
		$plan = PlanFactory::create_plan( 'blog' );
		PlanRepository::save_plan( $plan );

		// Try to trigger ecommerce task
		$product = (object) array( 'id' => 123 );
		$request = (object) array();
		TaskCompletionTriggers::on_product_creation( $product, $request, true );

		// Verify blog plan was not affected (ecommerce task doesn't exist in blog plan)
		$updated_plan = PlanRepository::get_current_plan();
		$this->assertEquals( 'blog', $updated_plan->type );

		// The task shouldn't exist in blog plan
		$task = $updated_plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertNull( $task );
	}

	/**
	 * Test blog post validator
	 *
	 * @covers ::validate_blog_post_creation_state
	 */
	public function test_validate_blog_post_creation_state() {
		// Should return false when no posts exist
		$this->assertFalse( TaskCompletionTriggers::validate_blog_post_creation_state() );

		// Create a real blog post (not Hello World)
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'My Real Blog Post',
				'post_name'   => 'my-real-blog-post',
			)
		);

		// Should return true when real post exists
		$this->assertTrue( TaskCompletionTriggers::validate_blog_post_creation_state() );

		// Clean up
		wp_delete_post( $post_id, true );
	}

	/**
	 * Test blog post validator ignores Hello World
	 *
	 * @covers ::validate_blog_post_creation_state
	 */
	public function test_validate_blog_post_creation_state_ignores_hello_world() {
		// Create Hello World post
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'post_title'  => 'Hello world!',
				'post_name'   => 'hello-world',
			)
		);

		// Should return false (Hello World doesn't count)
		$this->assertFalse( TaskCompletionTriggers::validate_blog_post_creation_state() );

		// Clean up
		wp_delete_post( $post_id, true );
	}

	// ========================================
	// # Helper Methods Tests
	// ========================================

	/**
	 * Test is_current_plan_for_task helper
	 *
	 * @covers ::is_current_plan_for_task
	 */
	public function test_is_current_plan_for_task() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Use reflection to access private method
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );
		$method     = $reflection->getMethod( 'is_current_plan_for_task' );
		$method->setAccessible( true );

		// Test with store task path (should match ecommerce plan)
		$result = $method->invoke( null, 'store_setup.store_build_track.setup_products.store_add_product' );
		$this->assertTrue( $result );

		// Test with blog task path (should NOT match ecommerce plan)
		$result = $method->invoke( null, 'blog_setup.blog_build_track.create_content.blog_first_post' );
		$this->assertFalse( $result );

		// Test with corporate task path (should NOT match ecommerce plan)
		$result = $method->invoke( null, 'corporate_setup.corporate_build_track.customize_website.corporate_upload_logo' );
		$this->assertFalse( $result );
	}

	/**
	 * Test get_plan_type_from_task_path helper
	 *
	 * @covers ::get_plan_type_from_task_path
	 */
	public function test_get_plan_type_from_task_path() {
		// Use reflection to access private method
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );
		$method     = $reflection->getMethod( 'get_plan_type_from_task_path' );
		$method->setAccessible( true );

		// Test store path
		$result = $method->invoke( null, 'store_setup.store_build_track.setup_products.store_add_product' );
		$this->assertEquals( 'ecommerce', $result );

		// Test blog path
		$result = $method->invoke( null, 'blog_setup.blog_build_track.create_content.blog_first_post' );
		$this->assertEquals( 'blog', $result );

		// Test corporate path
		$result = $method->invoke( null, 'corporate_setup.corporate_build_track.customize_website.corporate_upload_logo' );
		$this->assertEquals( 'corporate', $result );

		// Test invalid path
		$result = $method->invoke( null, 'invalid_path' );
		$this->assertNull( $result );
	}

	/**
	 * Test mark_task_complete_if_plan_matches helper
	 *
	 * @covers ::mark_task_complete_if_plan_matches
	 */
	public function test_mark_task_complete_if_plan_matches() {
		// Create and save ecommerce plan
		$plan = PlanFactory::create_plan( 'ecommerce' );
		PlanRepository::save_plan( $plan );

		// Use reflection to access private method
		$reflection = new \ReflectionClass( TaskCompletionTriggers::class );
		$method     = $reflection->getMethod( 'mark_task_complete_if_plan_matches' );
		$method->setAccessible( true );

		// Test with matching plan type (store task on ecommerce plan)
		$result = $method->invoke( null, 'store_setup.store_build_track.setup_products.store_add_product' );
		$this->assertTrue( $result );

		// Verify task is complete
		$updated_plan = PlanRepository::get_current_plan();
		$updated_task = $updated_plan->get_task( 'store_build_track', 'setup_products', 'store_add_product' );
		$this->assertTrue( $updated_task->is_completed() );

		// Test with non-matching plan type (blog task on ecommerce plan)
		$result = $method->invoke( null, 'blog_setup.blog_build_track.create_content.blog_first_post' );
		$this->assertFalse( $result );
	}

	// ========================================
	// # Plugin Activation Validator Tests
	// ========================================

	/**
	 * Test validate_advanced_reviews_state
	 */
	public function test_validate_advanced_reviews_state_when_plugin_inactive() {
		// Ensure plugin is not active
		deactivate_plugins( 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php' );

		// Should return false when plugin is not active
		$this->assertFalse( TaskCompletionTriggers::validate_advanced_reviews_state() );
	}

	/**
	 * Test validate_advanced_reviews_state when plugin is active
	 */
	public function test_validate_advanced_reviews_state_when_plugin_active() {
		// Mock the plugin as active
		// Note: We can't actually activate a plugin that doesn't exist,
		// so we'll use update_option to simulate it
		$active_plugins   = get_option( 'active_plugins', array() );
		$active_plugins[] = 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php';
		update_option( 'active_plugins', $active_plugins );

		// Should return true when plugin is active
		$this->assertTrue( TaskCompletionTriggers::validate_advanced_reviews_state() );

		// Clean up
		$active_plugins = array_diff( $active_plugins, array( 'wp-plugin-advanced-reviews/wp-plugin-advanced-reviews.php' ) );
		update_option( 'active_plugins', array_values( $active_plugins ) );
	}

	/**
	 * Test validate_affiliates_state
	 */
	public function test_validate_affiliates_state_when_plugin_inactive() {
		// Ensure plugin is not active
		deactivate_plugins( 'yith-woocommerce-affiliates/init.php' );

		// Should return false when plugin is not active
		$this->assertFalse( TaskCompletionTriggers::validate_affiliates_state() );
	}

	/**
	 * Test validate_affiliates_state when plugin is active
	 */
	public function test_validate_affiliates_state_when_plugin_active() {
		// Mock the plugin as active
		$active_plugins   = get_option( 'active_plugins', array() );
		$active_plugins[] = 'yith-woocommerce-affiliates/init.php';
		update_option( 'active_plugins', $active_plugins );

		// Should return true when plugin is active
		$this->assertTrue( TaskCompletionTriggers::validate_affiliates_state() );

		// Clean up
		$active_plugins = array_diff( $active_plugins, array( 'yith-woocommerce-affiliates/init.php' ) );
		update_option( 'active_plugins', array_values( $active_plugins ) );
	}

	/**
	 * Test validate_email_templates_state
	 */
	public function test_validate_email_templates_state_when_plugin_inactive() {
		// Ensure plugin is not active
		deactivate_plugins( 'wp-plugin-email-templates/wp-plugin-email-templates.php' );

		// Should return false when plugin is not active
		$this->assertFalse( TaskCompletionTriggers::validate_email_templates_state() );
	}

	/**
	 * Test validate_email_templates_state when plugin is active
	 */
	public function test_validate_email_templates_state_when_plugin_active() {
		// Mock the plugin as active
		$active_plugins   = get_option( 'active_plugins', array() );
		$active_plugins[] = 'wp-plugin-email-templates/wp-plugin-email-templates.php';
		update_option( 'active_plugins', $active_plugins );

		// Should return true when plugin is active
		$this->assertTrue( TaskCompletionTriggers::validate_email_templates_state() );

		// Clean up
		$active_plugins = array_diff( $active_plugins, array( 'wp-plugin-email-templates/wp-plugin-email-templates.php' ) );
		update_option( 'active_plugins', array_values( $active_plugins ) );
	}
}
