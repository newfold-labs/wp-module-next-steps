import { Title } from '@newfold/ui-component-library';
import { Section } from '../section';
import { chevron } from '../icons';

export const Track = ( { track, index, taskUpdateCallback, showDismissed } ) => {
	const isOpen = index === 0; // Open the first track by default

	return (
		<details className="nfd-track" open={ isOpen }>
			<summary className="nfd-track-header">
				<Title as="h2" className="nfd-track-title p-0">{ track.label }</Title>
				<span className="nfd-track-header-icon nfd-header-icon">{ chevron }</span>
			</summary>
			<div className="nfd-track-sections">
				{ track.sections.map( ( section, i ) => (
					<Section
						taskUpdateCallback={ taskUpdateCallback }
						key={ section.id }
						index={ i }
						section={ section }
						track={ track.id }
						showDismissed={ showDismissed }
					/>
				) ) }
			</div>
		</details>
	);
};
