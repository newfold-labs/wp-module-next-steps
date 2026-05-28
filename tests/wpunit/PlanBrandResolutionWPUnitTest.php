<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\DTOs\Plan;

/**
 * WordPress unit tests for brand-scoped plan loading via PlanFactory.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\PlanFactory
 */
class PlanBrandResolutionWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * Filter callback registered during a test.
	 *
	 * @var callable|null
	 */
	private $filter_callback;

	/**
	 * Remove brand plugin id filter after each test.
	 */
	public function tearDown(): void {
		if ( null !== $this->filter_callback ) {
			remove_filter( 'newfold_next_steps_brand_plugin_id', $this->filter_callback );
			$this->filter_callback = null;
		}
		parent::tearDown();
	}

	/**
	 * Simulate the host plugin id for plan resolution.
	 *
	 * @param string $plugin_id Plugin id to simulate.
	 */
	private function set_brand_plugin_id( string $plugin_id ): void {
		$this->filter_callback = function ( $filtered_id ) use ( $plugin_id ) {
			return $plugin_id;
		};
		add_filter( 'newfold_next_steps_brand_plugin_id', $this->filter_callback );
	}

	/**
	 * Find a task href by task id within a plan.
	 *
	 * @param Plan   $plan    Plan to search.
	 * @param string $task_id Task id.
	 * @return string|null Task href or null if not found.
	 */
	private function get_task_href( Plan $plan, string $task_id ): ?string {
		foreach ( $plan->tracks as $track ) {
			foreach ( $track->sections as $section ) {
				foreach ( $section->tasks as $task ) {
					if ( $task_id === $task->id ) {
						return $task->href;
					}
				}
			}
		}

		return null;
	}

	/**
	 * @covers ::create_plan
	 * @covers ::resolve_brand_plugin_id
	 */
	public function test_bluehost_blog_plan_uses_brand_urls() {
		$this->set_brand_plugin_id( 'bluehost' );

		$plan = PlanFactory::create_plan( 'blog' );
		$href = $this->get_task_href( $plan, 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
			$href
		);
	}

	/**
	 * @covers ::create_plan
	 */
	public function test_bluehost_corporate_security_plugin_link() {
		$this->set_brand_plugin_id( 'bluehost' );

		$plan = PlanFactory::create_plan( 'corporate' );
		$href = $this->get_task_href( $plan, 'corporate_install_security_plugin' );

		$this->assertSame(
			'{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace/security',
			$href
		);
	}

	/**
	 * @covers ::create_plan
	 */
	public function test_web_blog_plan_uses_brand_urls() {
		$this->set_brand_plugin_id( 'web' );

		$plan = PlanFactory::create_plan( 'blog' );
		$href = $this->get_task_href( $plan, 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.networksolutions.com/blog/how-to-improve-cro-rate-website/',
			$href
		);
	}

	/**
	 * @covers ::create_plan
	 */
	public function test_web_corporate_security_plugin_link() {
		$this->set_brand_plugin_id( 'web' );

		$plan = PlanFactory::create_plan( 'corporate' );
		$href = $this->get_task_href( $plan, 'corporate_install_security_plugin' );

		$this->assertSame(
			'{siteUrl}/wp-admin/admin.php?page=web#/marketplace/security',
			$href
		);
	}

	/**
	 * @covers ::create_plan
	 */
	public function test_crazy_domains_unmapped_task_uses_placeholder() {
		$this->set_brand_plugin_id( 'crazy-domains' );

		$plan = PlanFactory::create_plan( 'blog' );
		$href = $this->get_task_href( $plan, 'blog_display_testimonials' );

		$this->assertSame( '#', $href );
	}

	/**
	 * @covers ::create_plan
	 */
	public function test_vodien_corporate_security_plugin_link() {
		$this->set_brand_plugin_id( 'vodien' );

		$plan = PlanFactory::create_plan( 'corporate' );
		$href = $this->get_task_href( $plan, 'corporate_install_security_plugin' );

		$this->assertSame(
			'{siteUrl}/wp-admin/admin.php?page=vodien#/marketplace/security',
			$href
		);
	}

	/**
	 * @covers ::create_plan
	 */
	public function test_unknown_brand_blog_plan_falls_back_to_generic() {
		$this->set_brand_plugin_id( 'unknown-brand' );

		$plan = PlanFactory::create_plan( 'blog' );
		$href = $this->get_task_href( $plan, 'blog_welcome_subscribe_popup' );

		$this->assertSame( '#', $href );
	}

	/**
	 * @covers ::create_plan
	 */
	public function test_bluehost_ecommerce_loads_brand_store_plan() {
		$this->set_brand_plugin_id( 'bluehost' );

		$plan = PlanFactory::create_plan( 'ecommerce' );

		$this->assertSame( 'store_setup', $plan->id );
		$this->assertSame( 'ecommerce', $plan->type );
		$this->assertNotEmpty( $plan->tracks );
	}

	/**
	 * @covers ::plugin_id_to_namespace
	 */
	public function test_plugin_id_to_namespace_maps_hyphenated_ids() {
		$this->assertSame( 'Bluehost', PlanFactory::plugin_id_to_namespace( 'bluehost' ) );
		$this->assertSame( 'CrazyDomains', PlanFactory::plugin_id_to_namespace( 'crazy-domains' ) );
		$this->assertSame( 'Web', PlanFactory::plugin_id_to_namespace( 'web' ) );
	}
}
