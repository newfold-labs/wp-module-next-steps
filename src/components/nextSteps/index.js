import { Button } from '@newfold/ui-component-library';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { spinner, hideIcon } from '../icons';
import { Track } from '../track';
import './styles.scss';

/**
 * Wrapper method to post setting to endpoint
 *
 * @param {Object}   data         object of data
 * @param {Function} passError    setter for the error in component
 * @param {Function} thenCallback method to call in promise then
 */
const taskUpdateWrapper = ( data, passError, thenCallback ) => {
	return apiFetch( {
		url:
			window.NewfoldRuntime.restUrl +
			'newfold-next-steps/v1/steps/status',
		method: 'POST',
		data,
	} )
		.then( ( response ) => {
			console.log( 'Response from taskUpdateWrapper:', response );
			thenCallback( response );
		} )
		.catch( ( error ) => {
			passError( error );
		} );
};

export const NextSteps = () => {
	const [ plan, setPlan ] = useState( window.NewfoldNextSteps.plan );
	const [ showDismissed, setShowDismissed ] = useState( false );

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
				console.error( 'Error updating step:', error );
			},
			( response ) => {
				window.NewfoldNextSteps = response;
				setPlan( response.plan );
			}
		);
	};

	return (
		<div className="nfd-nextsteps" id="nfd-nextsteps">
			{ plan.tracks.length < 1 && spinner }
			<p className="nfd-pb-4">{ plan.description }</p>
			{ plan.tracks.map( ( track, i ) => (
				<Track
					key={ track.id }
					track={ track }
					index={ i }
					taskUpdateCallback={ taskUpdateCallback }
					showDismissed={ showDismissed }
				/>
			) ) }
			<div className="nfd-nextsteps-filters nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-center">
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
			</div>
		</div>
	);
};
