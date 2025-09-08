<?php

namespace NewfoldLabs\WP\Module\NextSteps\Data\Plans;

use NewfoldLabs\WP\Module\NextSteps\Plan;

/*
# Corporate

## Build

### Basic Site Setup 

- Complete Basic Site Setup 
- Set site name
- tagline
- language
- time zone

### Customize Your Website 

- Upload ‘Company’ Logo 
- Choose Brand Colors and Fonts 
- Customize Header and Footer 
- Customize Homepage Layout (select a template or blocks) 

### Set Up Key Pages 

- Create an About Us page 
- Create a Services or What We Do page 
- Create a Contact page with form and map 
- Add a Team or Leadership page (optional) 

### Configure Navigation 

- Add pages for Home, Blog, About, Contact  
- Create a Primary Menu 
- Add a Footer Menu (Privacy, Terms, etc.) 

### Add Legal & Trust Content 

- Add a Privacy Policy 
- Add Terms & Conditions 
- Add an Accessibility Statement 
- Add Client Logos or Testimonials


## Brand 

### Establish Your Brand Online 

- Set up a Custom Domain (if not done during onboarding) 
- Create a Favicon (browser icon) 
- Connect your Google Business Profile (if local/regional presence) 
- Create a Branded Email Address (e.g., info@yourcompany.com) 

### Launch Essential Marketing Tools 

- Set up Jetpack Stats (or GA4) 
- Connect Google Search Console 
- Install a SEO Plugin (Yoast Premium CTB path) 
- Add Social Sharing Settings 

### Set Up Contact & Engagement 

- Add a Contact Form with email routing 
- Embed a Map or Location (if applicable) 
- Add Live Chat or Contact Widget 
- Link to Social Media Profiles in header/footer 

### Final Review Before Going Live 

- Preview on Mobile, Tablet, and Desktop 
- Test Contact Form and Navigation Links 
- Disable "Coming Soon" mode 
- Ask someone outside your team to review the site 


## Grow  

### Strengthen Online Presence 

- Add Client Testimonials or Reviews 
- Add Certifications, Memberships, or Awards 

### Build Content for SEO & Trust 

- Publish your first Company Blog Post or Industry Insight 
- Create a FAQ Page 
- Optimize your key pages for keywords and readability 
- Generate and submit XML Sitemap to Google 

### Marketing & Lead Generation 

- Set up an Email Capture Form (newsletter) 
- Connect to CRM or Email Tool (e.g., Mailchimp, HubSpot) 
- Add a Call-to-Action Section to Homepage 
- Create a Downloadable Resource (e.g., brochure, whitepaper) 

### Site Performance & Security 

- Install Jetpack Boost or a caching plugin 
- Enable Automatic Backups & Update Alerts 
- Install a Security Plugin 
- Set up a Staging Site (for safe updates and redesigns) 

### Monitor & Improve 

- Review Traffic & Engagement in Google Analytics 
- Track form submissions or lead goals 
- Run a Speed Test (PageSpeed Insights, GTmetrix) 
- Plan your next content or campaign update 

*/
class CorporatePlan {

    /**
	 * Get default corporate or business plan
	 *
	 * @return Plan
	 */
    public static function get_plan() {
        return new Plan(
            array(
				'id'          => 'corporate_setup',
				'label'       => __( 'Corporate Setup', 'wp-module-next-steps' ),
				'description' => __( 'Set up your corporate website with these essential steps:', 'wp-module-next-steps' ),
				'tracks'      => array(
					array(
						'id'       => 'corporate_build_track',
						'label'    => __( 'Build', 'wp-module-next-steps' ),
						'open'     => false,
						'sections' => array(
							array(
								'id'    => 'basic_site_setup',
								'label' => __( 'Basic Site Setup', 'wp-module-next-steps' ),
								'open'  => true,
								'tasks' => array(
									array(
										'id'              => 'corporate_quick_setup',
										'title'           => __( 'Quick Setup', 'wp-module-next-steps' ),
										'href'            => '{siteUrl}/wp-admin/options-general.php',
										'status'          => 'new',
										'priority'        => 1,
										'source'          => 'wp-module-next-steps',
										'data_attributes' => array(
											'data-test-id' => 'corporate_quick_setup',
											'data-nfd-id'  => 'corporate_quick_start',
										),
									),
								),
							),
							array(
								'id'    => 'customize_website',
								'label' => __( 'Customize Your Website', 'wp-module-next-steps' ),
								'open'  => true,
								'tasks' => array(
									array(
										'id'       => 'corporate_upload_logo',
										'title'    => __( 'Upload Company Logo', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=all-parts',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_choose_brand_colors',
										'title'    => __( 'Choose Brand Colors and Fonts', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fstyles',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_customize_header',
										'title'    => __( 'Customize Header', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=header',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_customize_footer',
										'title'    => __( 'Customize Footer', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=footer',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_customize_homepage',
										'title'    => __( 'Customize Homepage Layout', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Ftemplate',
										'status'   => 'new',
										'priority' => 5,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'configure_navigation',
								'label' => __( 'Configure Navigation', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'corporate_add_navigation_pages',
										'title'    => __( 'Add Pages for Home, Blog, About, Contact', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/edit.php?post_type=page',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_create_primary_menu',
										'title'    => __( 'Create a Primary Menu', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=/navigation',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_add_footer_menu',
										'title'    => __( 'Add a Footer Menu', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php?p=%2Fpattern&postType=wp_template_part&categoryId=footer',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'add_legal_trust_content',
								'label' => __( 'Add Legal & Trust Content', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'corporate_privacy_policy',
										'title'    => __( 'Add a Privacy Policy', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/options-privacy.php',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_terms_conditions',
										'title'    => __( 'Add Terms & Conditions', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?wb-library=patterns&wb-category=text',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_accessibility_statement',
										'title'    => __( 'Add an Accessibility Statement', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?wb-library=patterns&wb-category=text',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
						),
					),
					array(
						'id'       => 'corporate_brand_track',
						'label'    => __( 'Brand', 'wp-module-next-steps' ),
						'sections' => array(
							array(
								'id'    => 'establish_brand_online',
								'label' => __( 'Establish Your Brand Online', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'corporate_setup_custom_domain',
										'title'    => __( 'Set Up a Custom Domain', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/my-account/domain-center-update/list',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_create_favicon',
										'title'    => __( 'Create a Favicon', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/customize.php?autofocus[section]=title_tagline',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_connect_google_business',
										'title'    => __( 'Connect Your Google Business Profile', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/transfer-google-business-profile-free-website-bluehost/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_create_branded_email',
										'title'    => __( 'Create a Branded Email Address', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-create-a-business-email-for-free/',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'launch_marketing_tools',
								'label' => __( 'Launch Essential Marketing Tools', 'wp-module-next-steps' ),
								'tasks' => array(

									/*
									Hide Jetpack Stats for now
									array(
										'id'       => 'corporate_setup_jetpack_stats',
										'title'    => __( 'Set Up Jetpack Stats', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=stats',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									*/
									array(
										'id'       => 'corporate_connect_search_console',
										'title'    => __( 'Connect Google Search Console', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-submit-your-website-to-search-engines/',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_install_seo_plugin',
										'title'    => __( 'Explore SEO Plugin', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=wpseo_dashboard',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),

									/*
									Hide Jetpack Social Sharing Settings for now
									array(
										'id'       => 'corporate_add_social_sharing',
										'title'    => __( 'Add Social Sharing Settings', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/site-editor.php?p=%2Fstyles&section=%2Fblocks%2Fjetpack%252Fsharing-buttons',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
									*/
								),
							),
							array(
								'id'    => 'setup_contact_engagement',
								'label' => __( 'Set Up Contact & Engagement', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'corporate_add_contact_form',
										'title'    => __( 'Add a Contact Form with email routing', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/create-contact-form-wordpress-guide/',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_embed_map',
										'title'    => __( 'Embed a Map or Location', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/top-wordpress-store-locator-plugins/',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_link_social_profiles',
										'title'    => __( 'Link to Social Media Profiles', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/site-editor.php',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
						),
					),
					array(
						'id'       => 'corporate_grow_track',
						'label'    => __( 'Grow', 'wp-module-next-steps' ),
						'sections' => array(
							array(
								'id'    => 'strengthen_online_presence',
								'label' => __( 'Strengthen Online Presence', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'corporate_add_client_testimonials',
										'title'    => __( 'Add Client Logos or Testimonials or Reviews', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?post_type=page',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_add_certifications',
										'title'    => __( 'Add Certifications, Memberships, or Awards', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?post_type=page',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'build_content_seo_trust',
								'label' => __( 'Build Content for SEO & Trust', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'corporate_publish_first_blog_post',
										'title'    => __( 'Publish Your First Company Blog Post', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?wb-library=patterns&wb-category=text',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_create_faq_page',
										'title'    => __( 'Create a FAQ Page', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/post-new.php?post_type=page&wb-library=patterns&wb-category=features',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_optimize_key_pages',
										'title'    => __( 'Optimize Your Key Pages for Keywords', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/content-optimization-guide/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_generate_submit_sitemap',
										'title'    => __( 'Generate and Submit XML Sitemap', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/what-is-a-sitemap-how-it-helps-seo-and-navigation/',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'marketing_lead_generation',
								'label' => __( 'Marketing & Lead Generation', 'wp-module-next-steps' ),
								'tasks' => array(
									array(
										'id'       => 'corporate_setup_email_capture',
										'title'    => __( 'Set Up an Email Capture Form', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-add-an-email-opt-in-form-to-your-website/',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_connect_crm',
										'title'    => __( 'Connect to CRM or Email Tool', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/marketing-automation-tools/',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_add_cta_section',
										'title'    => __( 'Add a Call-to-Action Section to Homepage', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/call-to-action-tips/',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'site_performance_security',
								'label' => __( 'Site Performance & Security', 'wp-module-next-steps' ),
								'tasks' => array(

									/*
									Hide Jetpack Boost and Automatic Backups for now
									array(
										'id'       => 'corporate_install_jetpack_boost',
										'title'    => __( 'Install Jetpack Boost or Caching Plugin', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=my-jetpack#/add-boost',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_enable_auto_backups',
										'title'    => __( 'Enable Automatic Backups & Update Alerts', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=jetpack-backup',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									*/
									array(
										'id'       => 'corporate_install_security_plugin',
										'title'    => __( 'Install a Security Plugin', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=bluehost#/marketplace/security',
										'status'   => 'new',
										'priority' => 3,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_setup_staging_site',
										'title'    => __( 'Set Up a Staging Site', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=nfd-staging',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
							array(
								'id'    => 'monitor_improve',
								'label' => __( 'Monitor & Improve', 'wp-module-next-steps' ),
								'tasks' => array(

									/*
									Hide Jetpack Traffic for now
									array(
										'id'       => 'corporate_review_traffic_engagement',
										'title'    => __( 'Review Traffic & Engagement', 'wp-module-next-steps' ),
										'href'     => '{siteUrl}/wp-admin/admin.php?page=stats',
										'status'   => 'new',
										'priority' => 1,
										'source'   => 'wp-module-next-steps',
									),
									*/
									array(
										'id'       => 'corporate_run_speed_test',
										'title'    => __( 'Run a Speed Test', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/what-is-my-page-speed/',
										'status'   => 'new',
										'priority' => 2,
										'source'   => 'wp-module-next-steps',
									),
									array(
										'id'       => 'corporate_plan_next_content',
										'title'    => __( 'Plan Your Next Content or Campaign Update', 'wp-module-next-steps' ),
										'href'     => 'https://www.bluehost.com/blog/how-to-create-a-content-calendar/',
										'status'   => 'new',
										'priority' => 4,
										'source'   => 'wp-module-next-steps',
									),
								),
							),
						),
					),
				),
			)
		);
    }
}
