<?php

namespace NewfoldLabs\WP\Module\NextSteps;

use NewfoldLabs\WP\Module\NextSteps\Data\Plans\PlanBrandUrls;

/**
 * WordPress unit tests for brand-specific plan task URL resolution.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\NextSteps\Data\Plans\PlanBrandUrls
 */
class PlanBrandUrlsWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

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
		PlanBrandUrls::clear_resolved_plugin_id_cache();
		parent::tearDown();
	}

	/**
	 * Simulate the host plugin id for URL resolution.
	 *
	 * @param string $plugin_id Plugin id to simulate.
	 */
	private function set_brand_plugin_id( string $plugin_id ): void {
		$this->filter_callback = function () use ( $plugin_id ) {
			return $plugin_id;
		};
		add_filter( 'newfold_next_steps_brand_plugin_id', $this->filter_callback );
	}

	/**
	 * Test Bluehost returns the Bluehost URL for a known task.
	 */
	public function test_bluehost_returns_bluehost_url_for_known_task() {
		$this->set_brand_plugin_id( 'bluehost' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
			$url
		);
	}

	/**
	 * Test web returns the mapped URL for a known task.
	 */
	public function test_web_returns_mapped_url_for_known_task() {
		$this->set_brand_plugin_id( 'web' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.networksolutions.com/blog/how-to-improve-cro-rate-website/',
			$url
		);
	}

	/**
	 * Test web returns hash for a task with no entry in the web URL map.
	 */
	public function test_web_returns_hash_for_unmapped_task() {
		$this->set_brand_plugin_id( 'web' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_nonexistent_task' );

		$this->assertSame( '#', $url );
	}

	/**
	 * Test unknown plugin id falls back to Bluehost URLs.
	 */
	public function test_unknown_plugin_id_falls_back_to_bluehost() {
		$this->set_brand_plugin_id( 'unknown-brand' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
			$url
		);
	}

	/**
	 * Test empty plugin id falls back to Bluehost URLs.
	 *
	 * Simulates resolve_plugin_id() returning '' when the container is unavailable
	 * (no brand filter is registered beyond forcing an empty id).
	 */
	public function test_empty_plugin_id_falls_back_to_bluehost() {
		$this->set_brand_plugin_id( '' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
			$url
		);
	}

	/**
	 * Test empty plugin id returns hash for an unknown task.
	 */
	public function test_empty_plugin_id_returns_hash_for_unknown_task() {
		$this->set_brand_plugin_id( '' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_nonexistent_task' );

		$this->assertSame( '#', $url );
	}

	/**
	 * Test web security plugin task uses the web admin marketplace URL.
	 */
	public function test_web_security_plugin_uses_brand_admin_page() {
		$this->set_brand_plugin_id( 'web' );

		$url = PlanBrandUrls::resolve_task_link( 'corporate_install_security_plugin' );

		$this->assertSame(
			'{siteUrl}/wp-admin/admin.php?page=web#/marketplace/security',
			$url
		);
	}

	/**
	 * Test crazy-domains returns hash for a task mapped to placeholder.
	 */
	public function test_crazy_domains_returns_hash_for_placeholder_task() {
		$this->set_brand_plugin_id( 'crazy-domains' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_display_testimonials' );

		$this->assertSame( '#', $url );
	}

	/**
	 * Test vodien returns the mapped URL for a known task.
	 */
	public function test_vodien_returns_mapped_url_for_known_task() {
		$this->set_brand_plugin_id( 'vodien' );

		$url = PlanBrandUrls::resolve_task_link( 'blog_welcome_subscribe_popup' );

		$this->assertSame(
			'https://www.vodien.com/learn/how-to-track-website-visitors-and-improve-conversions/',
			$url
		);
	}

	/**
	 * Test vodien returns hash for a task mapped to placeholder.
	 */
	public function test_vodien_returns_hash_for_placeholder_task() {
		$this->set_brand_plugin_id( 'vodien' );

		$url = PlanBrandUrls::resolve_task_link( 'corporate_setup_email_capture' );

		$this->assertSame( '#', $url );
	}

	/**
	 * Test vodien security plugin task uses the vodien admin marketplace URL.
	 */
	public function test_vodien_security_plugin_uses_brand_admin_page() {
		$this->set_brand_plugin_id( 'vodien' );

		$url = PlanBrandUrls::resolve_task_link( 'corporate_install_security_plugin' );

		$this->assertSame(
			'{siteUrl}/wp-admin/admin.php?page=vodien#/marketplace/security',
			$url
		);
	}
}
