import { useEffect, useState } from '@wordpress/element';
import { Title } from '@newfold/ui-component-library';
import { __ } from '@wordpress/i18n';
import { ProgressBar } from '../progressBar';
import { plusCircleIcon, minusCircleIcon, closeCircleIcon,trophyIcon } from '../icons';
import { Task } from '../task';

export const Section = ( props ) => {
	const {
		section,
		index,
		taskUpdateCallback,
		sectionOpenCallback,
		track,
		showDismissed,
		...restProps
	} = props;
	
	const [ showCompleteCelebration, setShowCompleteCelebration ] = useState( true );
	const [ isComplete, setIsComplete ] = useState( false );
	// Use persisted open state from section data, fallback to passed-in open prop or default for first section
	const [ isOpen, setIsOpen ] = useState( section.open !== undefined ? section.open : index === 0 );

	const completed = section.tasks.filter(
		( task ) => task.status === 'done'
	).length;
	const total = section.tasks.filter(
		( task ) => task.status !== 'dismissed'
	).length;

	// if section complete on load, don't show complete celebration
	useEffect( () => {
		if ( completed === total ) {
			setShowCompleteCelebration( false );
		}
	}, [] );

	useEffect( () => {
		if ( total === completed ) {
			const timer = setTimeout(() => {
				setIsComplete( !isComplete );
			}, 100);
			// Clean up the timer when the component unmounts
			return () => clearTimeout(timer);
		}
	}, [ showCompleteCelebration, completed, total ] );

	return (
		( total > 0 || showDismissed === true )&& (
		<details className="nfd-section" open={ isOpen }>
			<summary
				className="nfd-section-header" 
				onClick={ ( e ) => {
					sectionOpenCallback( section.id, !isOpen );
				} }
			>
				<Title className="nfd-section-title mb-0" as="h3">
					<span className="nfd-section-header-icon nfd-header-icon">
						<span className="nfd-section-header-icon-closed">
							{ plusCircleIcon }
						</span>
						<span className="nfd-section-header-icon-opened">
							{ minusCircleIcon }
						</span>
					</span>
					{ section.label }
				</Title>
				{ total > 0 && <ProgressBar completed={ completed } total={ total } /> }
			</summary>
			<div className="nfd-section-steps">
				{ section.tasks.map( ( step ) => (
					<Task
						key={ step.id }
						step={ step }
						taskUpdateCallback={ taskUpdateCallback }
						showDismissed={ showDismissed }
						track={ track }
						section={ section.id }
					/>
				) ) }
			</div>
			{ showCompleteCelebration && 
				<div
					className="nfd-section-complete"
					data-complete={ isComplete }
					onClick={ ( e ) => {
						setShowCompleteCelebration( false );
						setIsOpen( false )
						sectionOpenCallback( section.id, false );
					} }
				>
					<button className="nfd-nextsteps-section-close-button">
						{ closeCircleIcon }
					</button>
					<div className="nfd-section-celebrate">{ trophyIcon }</div>
					<p className="nfd-section-celebrate-text">{ __( 'All complete!', 'wp-module-next-steps' ) }</p>
				</div>
			}
		</details>
		)
	);
};
