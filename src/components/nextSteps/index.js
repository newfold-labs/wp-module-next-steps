import { useState } from '@wordpress/element';
import { Button } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { Step } from '../step';
import './styles.scss';

// sort steps by priority
const sortbyPriority = ( steps ) => {
	return steps.sort( ( a, b ) => {
		if ( a.priority === b.priority ) {
			return 0;
		}
		return parseInt( a.priority ) < parseInt( b.priority ) ? -1 : 1;
	} );
};
// sort steps by status
const sortbyStatus = ( steps ) => {
	return steps.sort( ( a, b ) => {
		if ( a.status < b.status ) {
			return 1;
		}
		if ( a.status > b.status ) {
			return -1;
		}
		return 0;
	} );
};

const postStatusUpdate = ( id, status ) => {
	// send updated step to endpoint
	const data = {
		id,
		status,
	};
	fetch(
		window.NewfoldRuntime.restUrl + 'newfold-next-steps/v1/steps/status',
		{
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.NewfoldRuntime.restNonce,
			},
			body: JSON.stringify( data ),
		}
	)
		.then( ( response ) => {
			if ( ! response.ok ) {
				throw new Error( 'Network response was not ok' );
			}
			return response.json();
		} )
		.then( ( data ) => {
			console.log( 'Step updated successfully:', data );
		} )
		.catch( ( error ) => {
			console.error( 'Error updating step:', error );
		} );
};

export const NextSteps = () => {
	const [ showNew, setShowNew ] = useState( true );
	const [ showDone, setShowDone ] = useState( false );
	const [ showDismissed, setShowDismissed ] = useState( false );
	const [ steps, setSteps ] = useState(
		sortbyStatus( sortbyPriority( window.NewfoldNextSteps ) )
	);
	// group by category?

	// listener for status change/checkbox
	const completeCallback = ( id, status ) => {
		// update status in steps
		const updatedSteps = steps.map( ( step ) => {
			if ( step.id === id ) {
				return { ...step, status };
			}
			return step;
		} );
		// update the steps in the window object
		setSteps( updatedSteps );
		// send updated step to endpoint
		postStatusUpdate( id, status );
	};

	const showAll = () => {
		setShowNew( true );
		setShowDone( true );
		setShowDismissed( true );
	};

	return (
		<div className="nfd-nextsteps" id="nfd-nextsteps">
			<p className="nfd-pb-4">
				{ __(
					'To get the best experience, we recommend completing these onboarding steps',
					'wp-module-next-steps'
				) }
			</p>
			<div className="nfd-nextsteps-steps nfd-grid nfd-gap-2 nfd-grid-cols-1">
				{ steps.map( ( step, i ) => {
					if (
						( ! showNew && step.status === 'new' ) ||
						( ! showDone && step.status === 'done' ) ||
						( ! showDismissed && step.status === 'dismissed' )
					) {
						return null;
					}
					return (
						<Step
							key={ step.id }
							id={ step.id }
							title={ step.title }
							description={ step.description }
							category={ step.category }
							status={ step.status }
							href={ step.href }
							completeCallback={ completeCallback }
						/>
					);
				} ) }
			</div>
			<div className="nfd-nextsteps-filters nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-center">
				<Button
					className="nfd-nextsteps-filter-button"
					data-nfd-click="nextsteps_step_toggle"
					data-nfd-event-category="nextsteps_toggle"
					data-nfd-event-key="toggle"
					onClick={ () => {
						setShowNew( ! showNew );
						setShowDone( ! showDone );
						setShowDismissed( ! showDismissed );
					} }
					variant="secondary"
				>
					{ showDone
						? __( 'View incomplete tasks', 'wp-module-next-steps' )
						: __( 'View complete tasks', 'wp-module-next-steps' ) }
				</Button>
			</div>
		</div>
	);
};
