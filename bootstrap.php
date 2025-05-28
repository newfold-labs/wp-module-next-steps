<?php

use NewfoldLabs\WP\ModuleLoader\Container;
use function NewfoldLabs\WP\ModuleLoader\register;
use NewfoldLabs\WP\Module\NextSteps\NextSteps;

if ( function_exists( 'add_action' ) )  {
	add_action(
		'plugins_loaded',
        function () {

			register(
				array(
					'name'     => 'next-steps',
					'label'    => __( 'Next Steps', 'wp-module-next-steps' ),
					'callback' => function ( Container $container ) {
						if ( ! defined( 'NFD_NEXTSTEPS_DIR' ) ) {
                            define( 'NFD_NEXTSTEPS_DIR', __DIR__ );
                        }
                        if ( ! defined( 'NFD_NEXTSTEPS_BUILD_DIR' ) ) {
                            define( 'NFD_NEXTSTEPS_BUILD_DIR', __DIR__ . '/build/' );
                        }
                        if ( ! defined( 'NFD_NEXTSTEPS_PLUGIN_URL' ) ) {
                            define( 'NFD_NEXTSTEPS_PLUGIN_URL', $container->plugin()->url );
                        }
                        if ( ! defined( 'NFD_NEXTSTEPS_PLUGIN_DIRNAME' ) ) {
                            define( 'NFD_NEXTSTEPS_PLUGIN_DIRNAME', dirname( $container->plugin()->basename ) );
						}
                        include_once NFD_NEXTSTEPS_DIR . '/includes/NextSteps.php';
						new NextSteps( $container );
					},
					'isActive' => true,
					'isHidden' => true,
				)
			);
		}
    );
}
