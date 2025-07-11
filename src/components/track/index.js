import { Section } from '../section';
import { chevron } from '../icons';

export const Track = ( { track, index, taskUpdateCallback } ) => {
	const isOpen = index === 0; // Open the first track by default

	return (
		<details className="nfd-track" open={ isOpen }>
			<summary className="nfd-track-header">
				<h2>{ track.label }</h2>
				<span className="nfd-track-header-icon">{ chevron }</span>
			</summary>
			<div className="nfd-track-sections">
				{ track.sections.map( ( section, i ) => (
					<Section
						taskUpdateCallback={ taskUpdateCallback }
						key={ section.id }
						index={ i }
						section={ section }
						track={ track.id }
					/>
				) ) }
			</div>
		</details>
	);
};
