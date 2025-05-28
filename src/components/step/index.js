import { Button, Checkbox, Title } from '@newfold/ui-component-library';
import classNames from 'classnames';
import { __, sprintf } from '@wordpress/i18n';

export const Step = ( {
	id,
	description = '',
	title = '',
	status,
	href,
	completeCallback,
} ) => {

	const getHref = () => {
		// replace {siteUrl} placeholder with the actual site URL
		if ( href.includes( '{siteUrl}' ) ) {
			return href.replace( '{siteUrl}', window.NewfoldRuntime.siteUrl );
		}
		return href;
	};
	const getTarget = () => {
		// if href is external, return target="_blank"
		if (
			href.includes( '{siteUrl}' ) ||
			href.includes( window.NewfoldRuntime.siteUrl )
		) {
			return '';
		}
		return '_blank';
	};
	const stepDoneRender = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-done nfd-flex nfd-flex-row nfd-justify-start nfd-items-center">
					<Checkbox
						className="nfd-nextsteps-step-checkbox"
						description={ description }
						id={ id }
						name={ id }
						value={ id }
						onChange={ ( e ) => completeCallback( id, 'new' ) }
						checked={ true }
						data-nfd-click="nextsteps_step_checkbox"
						data-nfd-event-key={ id }
						data-nfd-event-category="nextsteps_step"
					/>
					<div className="nfd-nextsteps-content nfd-flex nfd-flex-col nfd-justify-between">
						<Title as="h4" className="nfd-nextsteps-step-title">
							{ title }
						</Title>
						<span>{ description }</span>
					</div>
				</div>
			</div>
		);
	};
	const stepNewRender = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-new nfd-flex nfd-flex-row nfd-justify-start nfd-items-center">
					<Checkbox
						className="nfd-nextsteps-step-checkbox"
						id={ id }
						name={ id }
						value={ id }
						onChange={ ( e ) => completeCallback( id, 'done' ) }
						data-nfd-click="nextsteps_step_checkbox"
						data-nfd-event-key={ id }
						data-nfd-event-category="nextsteps_step"
					/>
					<div className="nfd-nextsteps-content nfd-flex nfd-flex-col nfd-justify-between">
						<Title as="h4" className="nfd-nextsteps-step-title">
							{ title }
						</Title>
						<span>{ description }</span>
					</div>
					<div className="nfd-nextsteps-buttons nfd-self-end">
						<Button
							as="a"
							className="nfd-nextsteps-step-link"
							href={ getHref() }
							size="small"
							target={ getTarget() }
							variant="secondary"
							data-nfd-click="nextsteps_step_link"
							data-nfd-event-key={ id }
							data-nfd-event-category="nextsteps_step"
							title={ description }
						>
							{ __( 'Go', 'wp-module-next-steps' ) }
						</Button>
						<Button
							className="nfd-nextsteps-step-dismiss"
							size="small"
							variant="secondary"
							data-nfd-click="nextsteps_step_dismiss"
							data-nfd-event-key={ id }
							data-nfd-event-category="nextsteps_step"
							onClick={ ( e ) =>
								completeCallback( id, 'dismissed' )
							}
						>
							{ __( 'x', 'wp-module-next-steps' ) }
						</Button>
					</div>
				</div>
			</div>
		);
	};
	const stepDismissedRender = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-dismissed nfd-flex nfd-flex-row nfd-justify-start nfd-items-center">
					<Checkbox
						className="nfd-nextsteps-step-checkbox"
						description={ description }
						id={ id }
						name={ id }
						value={ id }
						onChange={ ( e ) => completeCallback( id, 'new' ) }
						data-nfd-click="nextsteps_step_checkbox"
						data-nfd-event-key={ id }
						data-nfd-event-category="nextsteps_step"
					/>
					<div className="nfd-nextsteps-content nfd-flex nfd-flex-col nfd-justify-between">
						<Title as="h4" className="nfd-nextsteps-step-title">
							{ title }
						</Title>
						<span>{ description }</span>
					</div>
				</div>
			</div>
		);
	};

	return (
		<>
			{ status === 'new' && showNew && stepNewRender() }
			{ status === 'done' && showDone && stepDoneRender() }
			{ status === 'dismissed' && showDismissed && stepDismissedRender() }
		</>
	);
};
