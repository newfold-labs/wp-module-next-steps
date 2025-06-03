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
	const renderStepContent = ( href = false, target = '' ) => {
		return (
			<div className="nfd-nextsteps-step-content nfd-flex nfd-flex-col nfd-justify-between">
				{ href && (
					<a href={ href } target={ target }>
						<Title as="span" className="nfd-nextsteps-step-title">
							{ title }
						</Title>
					</a>
				) }
				{ ! href && (
					<Title as="span" className="nfd-nextsteps-step-title">
						{ title }
					</Title>
				) }
				<span>{ description }</span>
			</div>
		);
	};
	const renderNewStep = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-new nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-4">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end">
						<Button
							as="a"
							className="nfd-nextsteps-button nfd-nextsteps-button-check"
							data-nfd-click="nextsteps_step_check"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href="#"
							onClick={ ( e ) => completeCallback( id, 'done' ) }
							size="small"
							title={ __(
								'Mark Complete',
								'newfold-labs-next-steps'
							) }
						>
							<svg
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
									d="m4.5 12.75 6 6 9-13.5"
								/>
							</svg>
						</Button>
					</div>
					{ renderStepContent( getHref(), getTarget() ) }
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end nfd-ml-auto">
						<Button
							as="a"
							className="nfd-nextsteps-button nfd-nextsteps-button-link"
							data-nfd-click="nextsteps_step_link"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href={ getHref() }
							size="small"
							target={ getTarget() }
							title={ title }
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
							className="nfd-nextsteps-button nfd-nextsteps-button-dismiss"
							data-nfd-click="nextsteps_step_dismiss"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href="#"
							onClick={ ( e ) =>
								completeCallback( id, 'dismissed' )
							}
							size="small"
							title={ __( 'Dismiss', 'newfold-labs-next-steps' ) }
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
	const renderDoneStep = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-done nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-4">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end nfd-mr-4">
						<Button
							as="a"
							className="nfd-nextsteps-button nfd-nextsteps-button-redo"
							data-nfd-click="nextsteps_step_redo"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href="#"
							onClick={ ( e ) => completeCallback( id, 'new' ) }
							size="small"
							title={ __( 'Restart', 'newfold-labs-next-steps' ) }
						>
							<svg
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
									d="m4.5 12.75 6 6 9-13.5"
								/>
							</svg>
						</Button>
					</div>
					{ renderStepContent() }
				</div>
			</div>
		);
	};
	const renderDismissedStep = () => {
		return (
			<div className="nfd-nextsteps-step-container" id={ id }>
				<div className="nfd-nextsteps-step nfd-nextsteps-step-dismissed nfd-flex nfd-flex-row nfd-justify-start nfd-items-center nfd-gap-4">
					<div className="nfd-nextsteps-buttons nfd-flex nfd-flex-row nfd-gap-2 nfd-justify-end nfd-mr-4">
						<Button
							as="a"
							className="nfd-nextsteps-button nfd-nextsteps-button-redo"
							data-nfd-click="nextsteps_step_redo"
							data-nfd-event-category="nextsteps_step"
							data-nfd-event-key={ id }
							href="#"
							onClick={ ( e ) => completeCallback( id, 'new' ) }
							size="small"
							title={ __( 'Restart', 'newfold-labs-next-steps' ) }
						>
							<svg
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								strokeWidth="1.5"
								stroke="currentColor"
								className="size-6"
							>
								<path
									strokeLinecap="round"
									strokeLinejoin="round"
									d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"
								/>
							</svg>
						</Button>
					</div>
					{ renderStepContent() }
				</div>
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
