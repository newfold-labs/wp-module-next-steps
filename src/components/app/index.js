import { Page } from '@newfold/ui-component-library';
import { NextSteps } from '../nextSteps';
import classNames from 'classnames';
import { useState } from '@wordpress/element';
import './styles.scss';

export const NextStepsApp = () => {
	const classes = classNames( 'nfd-next-steps-app-container' );

	return (
		<Page className={ classes }>
			<NextSteps />

		</Page>
	);
};
