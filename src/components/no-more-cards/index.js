import { Title } from "@newfold/ui-component-library";
import { __ } from '@wordpress/i18n';
import { NoMoreCardsIcon } from '../section-card/wireframes';

export const NoMoreCards = () => {

	return (
		<div className="nfd-nextsteps" id="nfd-nextsteps">
            <div className="nfd-nextsteps-step-content--no-cards">
                <Title size={"2"} as="h3" className="nfd-mb-4">
                    { __( 'No Pending tasks', 'wp-module-next-steps' ) }
                </Title>

                <div className="nfd-nextsteps-step-card-none nfd-items-center nfd-flex nfd-flex-col nfd-justify-between">
                    <div className="nfd-nextsteps-step-card__wireframe nfd-mb-4">
                        <NoMoreCardsIcon />
                    </div>
                    <Title size={"3"} as="span" className="nfd-mb-1 nfd-font-bold">
                        { __( 'Hooray!', 'wp-module-next-steps' ) }
                    </Title>
                    <p className="nfd-text-center nfd-next-steps-no-cards-text">
                        { __( 'You don’t have any pending tasks today', 'wp-module-next-steps' )  }
                    </p>
                </div>
            </div>
		</div>
	);
};
