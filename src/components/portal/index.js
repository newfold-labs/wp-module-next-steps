import { createPortal, useEffect, useState } from '@wordpress/element';
import { NextSteps } from '../nextSteps';

export const NextStepsPortalApp = () => {
	const [ container, setContainer ] = useState( null );

	useEffect( () => {
		const registry = window.NFDPortalRegistry;
		// Check for required registry
		if ( ! registry ) {
			return;
		}

		const updateContainer = ( el ) => {
			setContainer( el );
		};

		const clearContainer = () => {
			setContainer( null );
		};

		// Subscribe to portal readiness updates
		registry.onReady( 'next-steps', updateContainer );
		registry.onRemoved( 'next-steps', clearContainer );

		// Immediately try to get the container if already registered
		const current = registry.getElement( 'next-steps' );
		if ( current ) {
			updateContainer( current );
		}
	}, [] );

	if ( ! container ) {
		return null;
	}

	return createPortal(
		<div className="next-steps-fill">
			<NextSteps />
		</div>,
		container
	);
};
