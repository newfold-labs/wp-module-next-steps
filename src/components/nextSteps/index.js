import { useState } from '@wordpress/element';
import { Step } from '../step';
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
	const [ steps, setSteps ] = useState(
		sortbyStatus( sortbyPriority( window.NewfoldNextSteps ) )
	);
	// group by category?
	// add listener for status change/checkbox
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

	return (
		<div className="nfd-nextsteps-steps nfd-grid nfd-gap-6 nfd-grid-cols-1">
			{ steps.map( ( step, i ) => {
				return (
					<Step
						key={ i }
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
	);
};
