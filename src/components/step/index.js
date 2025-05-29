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
	const renderDoneStep = () => {
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
					{ renderStepContent() }
				</div>
			</div>
		);
	};
	const renderNewStep = () => {
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
					{ renderStepContent() }
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-justify-end">
						<Button
							as="a"
							className="nfd-nextsteps-button-link"
							data-nfd-click="nextsteps_step_link"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href={ getHref() }
							size="small"
							target={ getTarget() }
							title={ description }
						>
							<svg // https://heroicons.com/
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								strokeWidth={ 1.5 }
								stroke="currentColor"
								className="size-6"
							>
								<path
									strokeLinecap="round"
									strokeLinejoin="round"
									d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"
								/>
							</svg>
						</Button>
						<Button
							as="a"
							className="nfd-nextsteps-button-dismiss"
							data-nfd-click="nextsteps_step_dismiss"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href="#"
							onClick={ ( e ) =>
								completeCallback( id, 'dismissed' )
							}
							size="small"
							title={ __( 'Dismiss', 'newfold-labs-next-steps' ) }
							variant="error"
						>
							<svg // https://heroicons.com/
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								strokeWidth={ 1.5 }
								stroke="currentColor"
								className="size-6"
							>
								<path
									strokeLinecap="round"
									strokeLinejoin="round"
									d="M6 18 18 6M6 6l12 12"
								/>
							</svg>
						</Button>
					</div>
				</div>
			</div>
		);
	};
	const renderDismissedStep = () => {
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
					{ renderStepContent() }
				</div>
			</div>
		);
	};
	const renderStepContent = () => {
		return (
			<div className="nfd-nextsteps-step-content nfd-flex nfd-flex-col nfd-justify-between">
				<Title as="span" className="nfd-nextsteps-step-title">
					{ title }
				</Title>
				<span>{ description }</span>
			</div>
		);
	};

	return (
		<>
			{ status === 'new' && renderNewStep() }
			{ status === 'done' && renderDoneStep() }
			{ status === 'dismissed' && renderDismissedStep() }
		</>
	);
};
