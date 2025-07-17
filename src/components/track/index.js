import { Title } from '@newfold/ui-component-library';
import { Section } from '../section';
import { chevron } from '../icons';

export const Track = ( props ) => {
	const {
		track,
		index,
		taskUpdateCallback,
		sectionOpenCallback,
		showDismissed,
		...restProps
	} = props;

	const isOpen = index === 0; // Open the first track by default

	return (
		<details className="nfd-track" open={ isOpen } { ...restProps }>
			<summary className="nfd-track-header">
				<Title className="nfd-track-title mb-0" as="h2">
					{ track.label }
				</Title>
				<span className="nfd-track-header-icon nfd-header-icon">
					{ chevron }
				</span>
			</summary>
			<div className="nfd-track-sections">
				{ track.sections.map( ( section, sectionIndex ) => (
					<Section
						key={ section.id }
						section={ section }
						index={ sectionIndex }
						taskUpdateCallback={ taskUpdateCallback }
						sectionOpenCallback={ sectionOpenCallback }
						track={ track.id }
						showDismissed={ showDismissed }
					/>
				) ) }
			</div>
		</details>
	);
};
