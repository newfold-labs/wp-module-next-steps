import { memo } from '@wordpress/element';
import { Title } from '@newfold/ui-component-library';
import { chevronIcon } from '../icons';
import { Section } from '../section';
import { ErrorBoundary } from '../ErrorBoundary';

export const Track = memo(( props ) => {
	const {
		index,
		track,
		sectionOpenCallback,
		showDismissed,
		taskUpdateCallback,
		trackOpenCallback,
	} = props;

	const handleToggleOpen = ( event ) => {
		// Get the new open state from the details element
		const newOpenState = event.target.open;
		// Call the callback to update the backend
		trackOpenCallback( track.id, newOpenState );
	};

	return (
		<details
			className="nfd-track"
			onToggle={ handleToggleOpen }
			data-nfd-track-id={ track.id }
			data-nfd-track-index={ index }
			open={ track.open }
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
					<ErrorBoundary 
						key={ `section-boundary-${section.id}` }
						fallback={ 
							<div className="nfd-section-error">
								<p>Section temporarily unavailable</p>
							</div> 
						}
					>
						<Section
							index={ sectionIndex }
							key={ section.id }
							section={ section }
							sectionOpenCallback={ sectionOpenCallback }
							showDismissed={ showDismissed }
							taskUpdateCallback={ taskUpdateCallback }
							trackId={ track.id }
						/>
					</ErrorBoundary>
				) ) }
			</div>
		</details>
	);
});
