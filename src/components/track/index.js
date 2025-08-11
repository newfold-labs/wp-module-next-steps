import { useEffect, useRef } from '@wordpress/element';
import { Title } from '@newfold/ui-component-library';
import { chevronIcon } from '../icons';
import { Section } from '../section';

export const Track = ( props ) => {
	const {
		index,
		track,
		sectionOpenCallback,
		showDismissed,
		taskUpdateCallback,
		trackOpenCallback,
	} = props;

	// Use track.open if available, otherwise fall back to default behavior (first track open)
	const initialOpenState = track.open;
	const detailsRef = useRef( null );
	const isInitialized = useRef( false );

	// Set initial open state imperatively
	useEffect( () => {
		if ( detailsRef.current ) {
			detailsRef.current.open = initialOpenState;
		}
		// Use setTimeout to ensure initialization happens after any triggered events
		setTimeout( () => {
			isInitialized.current = true;
		}, 0 );
	}, [] );

	const handleToggleOpen = ( event ) => {
		// Only call the callback if this is a user-triggered event (after initialization)
		if ( ! isInitialized.current ) {
			return;
		}
		
		// Get the new open state from the details element
		const newOpenState = event.target.open;
		// Call the callback to update the backend
		trackOpenCallback( track.id, newOpenState );
	};

	return (
		<details
			ref={ detailsRef }
			className="nfd-track"
			onToggle={ handleToggleOpen }
			data-nfd-track-id={ track.id }
			data-nfd-track-index={ index }
		>
			<summary className="nfd-track-header">
				<Title className="nfd-track-title mb-0" as="h2">
					{ track.label }
				</Title>
				<span className="nfd-track-header-icon nfd-header-icon">
					{ chevronIcon }
				</span>
			</summary>
			<div className="nfd-track-sections">
				{ track.sections.map( ( section, sectionIndex ) => (
					<Section
						index={ sectionIndex }
						key={ section.id }
						section={ section }
						sectionOpenCallback={ sectionOpenCallback }
						showDismissed={ showDismissed }
						taskUpdateCallback={ taskUpdateCallback }
						trackId={ track.id }
					/>
				) ) }
			</div>
		</details>
	);
};
