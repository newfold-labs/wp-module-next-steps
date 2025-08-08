import { Title } from '@newfold/ui-component-library';
import { Section } from '../section';
import { chevronIcon } from '../icons';

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
	const isOpen = track.hasOwnProperty('open') ? track.open : index === 0;

	const handleToggleOpen = ( event ) => {
		// Get the new open state from the details element
		const newOpenState = event.target.open;
		// Call the callback to update the backend
		trackOpenCallback( track.id, newOpenState );
	};

	return (
		<details 
			className="nfd-track"
			open={ isOpen } 
			onToggle={ handleToggleOpen }
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
