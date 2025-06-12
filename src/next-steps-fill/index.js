import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { NextStepsFillApp } from '../components/fill';

const WP_NEXTSTEPS_FILL_ELEMENT = 'nfd-portal-app';
let root = null;

const App = () => {
	return <NextStepsFillApp />;
};

const NextStepsFillAppRender = () => {
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

// window.addEventListener( 'nfd:slots-ready', NextStepsFillAppRender );
domReady( NextStepsFillAppRender );
