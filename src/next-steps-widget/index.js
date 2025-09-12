import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { Root } from '@newfold/ui-component-library';
import { NextStepsListApp } from '../components/nextStepsListApp';

const WP_NEXTSTEPS_ROOT_ELEMENT = 'nfd-next-steps-app';

const App = () => {
	return (
		<Root>
			<NextStepsListApp />
		</Root>
	);
};

const NextStepsAppRender = () => {
	const DOM_ELEMENT = document.getElementById( WP_NEXTSTEPS_ROOT_ELEMENT );
	if ( null !== DOM_ELEMENT ) {
		if ( 'undefined' !== typeof createRoot ) {
			createRoot( DOM_ELEMENT ).render( <App /> );
		}
	}
};

domReady( NextStepsAppRender );
