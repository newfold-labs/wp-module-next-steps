import { Button } from '@newfold/ui-component-library';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { spinner, hideIcon } from '../icons';
import { Track } from '../track';
import './styles.scss';

/**
 * Method to create endpoint url
 * 
 * no permalinks: 'http://localhost:8882/index.php?rest_route=/'
 * permalinks: 'http://localhost:8882/wp-json/'
 */
const createEndpointUrl = ( root, endpoint ) => {
	// if restUrl has /index.php?rest_route=/, add escaped endpoint
	if ( root.includes( '?' ) ) {
		return root + encodeURIComponent( endpoint );
	} 
	// otherwise permalinks set and restUrl should concatenate endpoint
	return root + endpoint;
};

/**
 * Wrapper method to post task update to endpoint
 *
 * @param {Object}   data         object of data
 * @param {Function} passError    setter for the error in component
 * @param {Function} thenCallback method to call in promise then
 */
const taskUpdateWrapper = ( data, passError, thenCallback ) => {
	return apiFetch( {
		url: createEndpointUrl( 
			window.NewfoldRuntime.restUrl,
			'newfold-next-steps/v1/steps/status'
		),
		method: 'PUT',
		data,
	} )
		.then( ( response ) => {
			// console.log( 'Response from taskUpdateWrapper:', response );
			thenCallback( response );
		} )
		.catch( ( error ) => {
			// console.error( 'Error from taskUpdateWrapper:', error );
			passError( error );
		} );
};

/**
* Wrapper method to post section update to endpoint
*
* @param {Object}   data         object of data
* @param {Function} passError    setter for the error in component
* @param {Function} thenCallback method to call in promise then
*/
const sectionUpdateWrapper = ( data, passError, thenCallback ) => {
	return apiFetch( {
		url: createEndpointUrl( 
			window.NewfoldRuntime.restUrl, 
			'newfold-next-steps/v1/steps/section/open'
		),
		method: 'PUT',
		data,
	} )
		.then( ( response ) => {
			// console.log( 'Section update response:', response );
			thenCallback( response );
		} )
		.catch( ( error ) => {
			// console.error( 'Error updating section:', error );
			passError( error );
		} );
};

export const NextSteps = () => {
	const [ plan, setPlan ] = useState( window.NewfoldNextSteps );
	const [ showDismissed, setShowDismissed ] = useState( false );
	const [ showControls, setShowControls ] = useState( false );

	const taskUpdateCallback = ( track, section, id, status ) => {
		const data = {
			plan: plan.id,
			track,
			section,
			task: id,
			status,
		};
		taskUpdateWrapper(
			data,
			( error ) => {
				// TODO handle error better
				// console.error( 'Error updating step:', error );
			},
			( response ) => {
				// The response is the full plan object, not wrapped in a plan property
				// console.log( 'Task update response:', response );
				window.NewfoldNextSteps = response;
				setPlan( response );
			}
		);
	};

	const sectionOpenCallback = ( section, open ) => {
		// console.log( 'Section open callback:', section, open );
		
		// Find the track that contains this section
		let trackId = null;
		if ( plan && plan.tracks ) {
			for ( const track of plan.tracks ) {
				if ( track.sections && track.sections.some( s => s.id === section ) ) {
					trackId = track.id;
					break;
				}
			}
		}
		
		if ( ! trackId ) {
			console.error( 'Could not find track for section:', section );
			return;
		}

		const data = {
			plan: plan.id,
			track: trackId,
			section: section,
			open: open,
		};
		
		sectionUpdateWrapper( 
			data,
			( error ) => {
				// console.error( 'Error updating section open state:', error );
			},
			( response ) => {
				// console.log( 'Section open state updated successfully:', response );
			}
		);
	};

	// Handle case where plan might not be loaded yet
	if ( ! plan || ! plan.tracks ) {
		return (
			<div className="nfd-nextsteps" id="nfd-nextsteps">
				{ spinner }
				<p>{ __( 'Loading next steps...', 'wp-module-next-steps' ) }</p>
			</div>
		);
	}

	return (
		<div className="nfd-nextsteps" id="nfd-nextsteps">
			<p className="nfd-pb-4">{ plan.description }</p>
			{ plan.tracks.map( ( track, i ) => (
				<Track
					key={ track.id }
					track={ track }
					index={ i }
					taskUpdateCallback={ taskUpdateCallback }
					sectionOpenCallback={ sectionOpenCallback }
					showDismissed={ showDismissed }
				/>
			) ) }
			{ showControls && <div className="nfd-nextsteps-filters nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-center">
				<Button
					className="nfd-nextsteps-filter-button"
					data-nfd-click="nextsteps_step_toggle"
					data-nfd-event-category="nextsteps_toggle"
					data-nfd-event-key="toggle"
					onClick={ () => {
						setShowDismissed( ! showDismissed );
					} }
					variant="secondary"
				>{ hideIcon }
					{ showDismissed
						? __( 'Hide skipped tasks', 'wp-module-next-steps' )
						: __( 'View skipped tasks', 'wp-module-next-steps' )
					}
				</Button>
			</div> }
		</div>
	);
};
