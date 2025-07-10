import { useState } from '@wordpress/element';
import Section from './section';

const Track = ( { track } ) => {
	const [ open, setOpen ] = useState( false );

	return (
		<div className="nfd-track">
			<div
				className="nfd-track-header"
				onClick={ () => setOpen( ! open ) }
			>
				<h2>{ track.label }</h2>
				<span>{ open ? '▲' : '▼' }</span>
			</div>
			{ open && (
				<div className="nfd-track-sections">
					{ track.sections.map( ( section ) => (
						<Section key={ section.id } section={ section } />
					) ) }
				</div>
			) }
		</div>
	);
};

export default Track;
