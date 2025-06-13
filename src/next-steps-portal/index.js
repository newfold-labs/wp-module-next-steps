import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { NextStepsPortalApp } from '../components/portal';

const WP_NEXTSTEPS_FILL_ELEMENT = 'nfd-next-steps-portal';
let root = null;

const App = () => {
	return <NextStepsPortalApp />;
};

const NextStepsPortalAppRender = () => {
	const DOM_ELEMENT = document.getElementById( WP_NEXTSTEPS_FILL_ELEMENT );
	if ( null !== DOM_ELEMENT ) {
		if ( 'undefined' !== typeof createRoot ) {
			if ( ! root ) {
				root = createRoot( DOM_ELEMENT );
			}
			root.render( <App /> );
		}
	}
};

// window.addEventListener( 'nfd:slots-ready', NextStepsPortalAppRender );
domReady( NextStepsPortalAppRender );
