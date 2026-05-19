<?php
namespace NewfoldLabs\WP\Module\NextSteps\Data\Plans;

use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Resolves external / brand-specific task URLs by host plugin id (`container()->plugin()->id`).
 *
 * Blog and corporate task ids are unique (`blog_*`, `corporate_*`). Each brand in
 * BRAND_TASK_URLS may define only the tasks it has content for; missing entries resolve to `#`.
 * Unrecognized plugin ids fall back to the Bluehost map.
 */
class PlanBrandUrls {

	/**
	 * Plugin ids with explicit brand URL maps.
	 */
	private const BRAND_PLUGIN_IDS = array(
		'bluehost',
		'web',
		'crazy-domains',
		'vodien',
	);

	/**
	 * Per-brand task URLs. Keys must match task `id` in BlogPlan / CorporatePlan.
	 *
	 * @var array<string, array<string, string>>
	 */
	private const BRAND_TASK_URLS = array(
		'bluehost'     => array(
			'blog_welcome_subscribe_popup'      => 'https://www.bluehost.com/blog/improve-conversion-rate-website-pop-ups/',
			'blog_embed_social_feed'            => 'https://www.bluehost.com/blog/how-to-incorporate-a-social-media-marketing-strategy-with-your-wordpress-website/',
			'blog_submit_search_console'        => 'https://www.bluehost.com/blog/how-to-submit-your-website-to-search-engines/',
			'blog_generate_sitemap'             => 'https://www.bluehost.com/blog/what-is-a-sitemap-how-it-helps-seo-and-navigation/',
			'blog_display_testimonials'         => 'https://www.bluehost.com/blog/customer-testimonials/',
			'blog_build_newsletter'             => 'https://www.bluehost.com/blog/how-to-create-an-email-newsletter/',
			'blog_draft_outreach_list'          => 'https://www.bluehost.com/blog/guest-blogging/',
			'blog_run_first_ad'                 => 'https://www.bluehost.com/blog/social-media-advertising-tips/',
			'blog_track_utm_campaigns'          => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
			'blog_plan_content_series'          => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
			'blog_implement_internal_linking'   => 'https://www.bluehost.com/blog/internal-linking-guide/',
			'blog_create_staging_site'          => 'https://www.bluehost.com/blog/what-is-a-staging-site-and-how-to-create-a-bluehost-staging-site-for-your-wordpress-website/',
			'corporate_setup_custom_domain'     => 'https://www.bluehost.com/my-account/domain-center-update/list',
			'corporate_connect_google_business' => 'https://www.bluehost.com/blog/transfer-google-business-profile-free-website-bluehost/',
			'corporate_create_branded_email'    => 'https://www.bluehost.com/blog/how-to-create-a-business-email-for-free/',
			'corporate_connect_search_console'  => 'https://www.bluehost.com/blog/how-to-submit-your-website-to-search-engines/',
			'corporate_add_contact_form'        => 'https://www.bluehost.com/blog/create-contact-form-wordpress-guide/',
			'corporate_embed_map'               => 'https://www.bluehost.com/blog/top-wordpress-store-locator-plugins/',
			'corporate_optimize_key_pages'      => 'https://www.bluehost.com/blog/content-optimization-guide/',
			'corporate_generate_submit_sitemap' => 'https://www.bluehost.com/blog/what-is-a-sitemap-how-it-helps-seo-and-navigation/',
			'corporate_setup_email_capture'     => 'https://www.bluehost.com/blog/how-to-add-an-email-opt-in-form-to-your-website/',
			'corporate_connect_crm'             => 'https://www.bluehost.com/blog/marketing-automation-tools/',
			'corporate_add_cta_section'         => 'https://www.bluehost.com/blog/call-to-action-tips/',
			'corporate_install_security_plugin' => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace/security',
			'corporate_run_speed_test'          => 'https://www.bluehost.com/blog/what-is-my-page-speed/',
			'corporate_plan_next_content'       => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
		),
		'web'          => array(
			'blog_welcome_subscribe_popup'      => 'https://www.networksolutions.com/blog/how-to-improve-cro-rate-website/',
			'blog_embed_social_feed'            => 'https://www.networksolutions.com/blog/use-social-media-small-business/',
			'blog_submit_search_console'        => 'https://www.networksolutions.com/blog/how-to-use-google-search-console/',
			'blog_generate_sitemap'             => 'https://www.networksolutions.com/blog/create-upload-sitemap/',
			'blog_display_testimonials'         => 'https://www.networksolutions.com/blog/business-website-pages/',
			'blog_build_newsletter'             => 'https://www.networksolutions.com/blog/create-a-newsletter/',
			'blog_draft_outreach_list'          => 'https://www.networksolutions.com/blog/ecommerce-link-building/',
			'blog_run_first_ad'                 => 'https://www.networksolutions.com/blog/use-social-media-small-business/',
			'blog_track_utm_campaigns'          => 'https://www.networksolutions.com/blog/create-content-calendar-small-business/',
			'blog_plan_content_series'          => 'https://www.networksolutions.com/blog/create-content-calendar-small-business/',
			'blog_implement_internal_linking'   => 'https://www.networksolutions.com/blog/internal-linking-seo-strategy-guide/',
			'blog_create_staging_site'          => 'https://www.networksolutions.com/blog/the-importance-of-website-staging/',
			'corporate_setup_custom_domain'     => 'https://www.networksolutions.com/domains/domain-management',
			'corporate_connect_google_business' => 'https://www.networksolutions.com/blog/add-attributes-google-my-business-listing/',
			'corporate_create_branded_email'    => 'https://www.networksolutions.com/blog/your-guide-to-business-email-for-small-businesses/',
			'corporate_connect_search_console'  => 'https://www.networksolutions.com/blog/how-to-use-google-search-console/',
			'corporate_add_contact_form'        => 'https://www.networksolutions.com/blog/website-contact-form/',
			'corporate_embed_map'               => 'https://www.networksolutions.com/blog/how-to-claim-google-business-profile/',
			'corporate_optimize_key_pages'      => 'https://www.networksolutions.com/blog/content-marketing-for-small-businesses/',
			'corporate_generate_submit_sitemap' => 'https://www.networksolutions.com/blog/create-upload-sitemap/',
			'corporate_setup_email_capture'     => 'https://www.networksolutions.com/blog/website-contact-form/',
			'corporate_connect_crm'             => 'https://www.networksolutions.com/blog/crm-small-business-helps-company-grow/',
			'corporate_add_cta_section'         => 'https://www.networksolutions.com/blog/write-5-killer-calls-to-action/',
			'corporate_install_security_plugin' => '{siteUrl}/wp-admin/admin.php?page=web#/marketplace/security',
			'corporate_run_speed_test'          => 'https://www.networksolutions.com/blog/ecommerce-site-too-slow/',
			'corporate_plan_next_content'       => 'https://www.networksolutions.com/blog/create-content-calendar-small-business/',
		),
		'crazy-domains' => array(
			'blog_welcome_subscribe_popup'      => 'https://www.crazydomains.in/learn/tips-optimise-site-conversions/',
			'blog_embed_social_feed'            => 'https://www.crazydomains.in/learn/right-social-media-platforms/',
			'blog_submit_search_console'        => 'https://www.crazydomains.com/learn/submitting-site-to-search-engines/',
			'blog_generate_sitemap'             => 'https://www.crazydomains.in/learn/courses/creating-branded-website/',
			'blog_display_testimonials'         => '#',
			'blog_build_newsletter'             => 'https://www.crazydomains.in/learn/elements-of-email-newsletter/',
			'blog_draft_outreach_list'          => 'https://www.crazydomains.in/learn/guest-posting/',
			'blog_run_first_ad'                 => 'https://www.crazydomains.in/learn/right-social-media-platforms/',
			'blog_track_utm_campaigns'          => 'https://www.crazydomains.in/learn/content-marketing-tips-small-business-website/',
			'blog_plan_content_series'          => 'https://www.crazydomains.in/learn/content-marketing-tips-small-business-website/',
			'blog_implement_internal_linking'   => '#',
			'blog_create_staging_site'          => '#',
			'corporate_setup_custom_domain'     => 'https://www.crazydomains.in/domain-names/products/',
			'corporate_connect_google_business' => 'https://www.crazydomains.in/learn/google-workspace-crazy-domains/',
			'corporate_create_branded_email'    => 'https://www.crazydomains.in/learn/email-seo-marketing/',
			'corporate_connect_search_console'  => 'https://www.crazydomains.com/learn/submitting-site-to-search-engines/',
			'corporate_add_contact_form'        => 'https://www.crazydomains.in/learn/high-converting-lead-capture-forms/',
			'corporate_embed_map'               => '#',
			'corporate_optimize_key_pages'      => 'https://www.crazydomains.in/learn/seo-content-marketing/',
			'corporate_generate_submit_sitemap' => '#',
			'corporate_setup_email_capture'     => 'https://www.crazydomains.in/learn/high-converting-lead-capture-forms/',
			'corporate_connect_crm'             => 'https://www.crazydomains.in/learn/email-marketing-campaign-tools/',
			'corporate_add_cta_section'         => 'https://www.crazydomains.in/learn/how-to-create-irresistible-ctas/',
			'corporate_install_security_plugin' => '{siteUrl}/wp-admin/admin.php?page=crazy-domains#/marketplace/security',
			'corporate_run_speed_test'          => 'https://www.crazydomains.in/learn/website-speed-optimisation-tips/',
			'corporate_plan_next_content'       => 'https://www.crazydomains.in/learn/content-marketing-tips-small-business-website/',
		),
		'vodien'       => array(
			'blog_welcome_subscribe_popup'      => 'https://www.vodien.com/learn/how-to-track-website-visitors-and-improve-conversions/',
			'blog_embed_social_feed'            => 'https://www.vodien.com/learn/social-media-feed-integration/',
			'blog_submit_search_console'        => 'https://www.vodien.com/learn/search-engines-and-how-to-do-it/',
			'blog_generate_sitemap'             => 'https://www.vodien.com/learn/xml-sitemap-for-seo/',
			'blog_display_testimonials'         => '#',
			'blog_build_newsletter'             => 'https://www.vodien.com/learn/email-newsletter-seo-integration/',
			'blog_draft_outreach_list'          => 'https://www.vodien.com/learn/make-guest-blogging-work-for-you/',
			'blog_run_first_ad'                 => 'https://www.vodien.com/learn/run-successful-social-media-ad-campaign/',
			'blog_track_utm_campaigns'          => 'https://www.vodien.com/learn/how-to-track-website-visitors-and-improve-conversions/',
			'blog_plan_content_series'          => 'https://www.vodien.com/learn/social-media-content-calendar/',
			'blog_implement_internal_linking'   => 'https://www.vodien.com/learn/internal-linking-strategies-for-ecommerce-sites/',
			'blog_create_staging_site'          => 'https://www.vodien.com/learn/a-beginners-guide-to-website-staging/',
			'corporate_setup_custom_domain'     => 'https://www.vodien.com/learn/custom-domain-name/',
			'corporate_connect_google_business' => 'https://www.vodien.com/learn/how-to-add-google-my-business-reviews-to-website/',
			'corporate_create_branded_email'    => 'https://www.vodien.com/learn/free-business-email/',
			'corporate_connect_search_console'  => 'https://www.vodien.com/learn/search-engines-and-how-to-do-it/',
			'corporate_add_contact_form'        => 'https://www.vodien.com/learn/how-to-stop-contact-form-spam-wordpress/',
			'corporate_embed_map'               => 'https://www.vodien.com/learn/creative-advertising-with-street-view-on-google-maps/',
			'corporate_optimize_key_pages'      => 'https://www.vodien.com/learn/seo-audit-process-in-17-simple-steps-boost-your-content-visibility-on-search-engines/',
			'corporate_generate_submit_sitemap' => 'https://www.vodien.com/learn/xml-sitemap-for-seo/',
			'corporate_setup_email_capture'     => '#',
			'corporate_connect_crm'             => 'https://www.vodien.com/learn/customer-relationship-management-integration/',
			'corporate_add_cta_section'         => '#',
			'corporate_install_security_plugin' => '{siteUrl}/wp-admin/admin.php?page=vodien#/marketplace/security',
			'corporate_run_speed_test'          => 'https://www.vodien.com/learn/do-not-ignore-page-speed/',
			'corporate_plan_next_content'       => 'https://www.vodien.com/learn/social-media-content-calendar/',
		),
	);

	/**
	 * Resolve task link for a task id based on current host plugin.
	 *
	 * @param string $task_id Task id from BlogPlan or CorporatePlan.
	 * @return string URL (may contain `{siteUrl}` for replacement elsewhere in the module).
	 */
	public static function resolve_task_link( string $task_id ): string {
		$plugin_id = self::resolve_plugin_id();

		if ( '' !== $plugin_id && self::has_brand_map( $plugin_id ) ) {
			$brand_urls = self::BRAND_TASK_URLS[ $plugin_id ];
			if ( isset( $brand_urls[ $task_id ] ) ) {
				return $brand_urls[ $task_id ];
			}

			return '#';
		}
		// Fallback to Bluehost map if no brand map is found.
		$bluehost_urls = self::BRAND_TASK_URLS['bluehost'];

		return $bluehost_urls[ $task_id ] ?? '#';
	}

	/**
	 * @return string Plugin id from container, or empty string if unavailable.
	 */
	private static function resolve_plugin_id(): string {
		$plugin_id = '';

		if ( function_exists( 'NewfoldLabs\WP\ModuleLoader\container' ) ) {
			$c = container();
			if ( is_object( $c ) && method_exists( $c, 'plugin' ) ) {
				$plugin = $c->plugin();
				if ( is_object( $plugin ) && isset( $plugin->id ) ) {
					$plugin_id = (string) $plugin->id;
				}
			}
		}

		/**
		 * Filter the host plugin id used for brand-specific plan task URLs.
		 *
		 * @param string $plugin_id Plugin id from the module loader container.
		 */
		return (string) apply_filters( 'newfold/next-steps/brand-plugin-id', $plugin_id );
	}

	private static function has_brand_map( string $plugin_id ): bool {
		return in_array( $plugin_id, self::BRAND_PLUGIN_IDS, true )
			&& array_key_exists( $plugin_id, self::BRAND_TASK_URLS );
	}
}
