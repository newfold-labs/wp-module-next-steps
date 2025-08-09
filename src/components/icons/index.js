import {
	ArrowRightIcon,
	ChevronUpIcon,
	PlusCircleIcon,
	MinusCircleIcon,
	EyeSlashIcon,
	EyeIcon,
	TrophyIcon,
	XCircleIcon
} from '@heroicons/react/24/outline';
import { CheckCircleIcon } from '@heroicons/react/24/solid';

// Using Heroicons components
export const chevronIcon = <ChevronUpIcon className="size-6"/>;
export const plusCircleIcon = <PlusCircleIcon className="size-6"/>;
export const minusCircleIcon = <MinusCircleIcon className="size-6"/>;
export const hideIcon = <EyeSlashIcon className="size-6"/>;
export const showIcon = <EyeIcon className="size-6"/>;
export const doneIcon = <CheckCircleIcon className="size-6"/>;
export const goIcon = <ArrowRightIcon className="size-6"/>;
export const trophyIcon = <TrophyIcon className="size-6"/>;
export const closeCircleIcon = <XCircleIcon className="size-6"/>;

// Circle dashed icon from https://sidekickicons.com/?iconset=Sidekickicons&code=JSX&icon=circle-dashed
export const circleDashedIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		strokeWidth={ 1.5 }
		stroke="currentColor"
		aria-hidden="true"
		fill="none"
		className="size-6"
	>
		<path
			strokeLinecap="round"
			strokeLinejoin="round"
			d="M13.716 3.165a9 9 0 0 1 3.315 1.373m2.43 2.429a9 9 0 0 1 1.374 3.316m0 3.434a9 9 0 0 1-1.373 3.315m-2.43 2.43a9 9 0 0 1-3.316 1.373m-3.432 0a9 9 0 0 1-3.316-1.373m-2.43-2.43a9 9 0 0 1-1.373-3.315m0-3.434a9 9 0 0 1 1.373-3.315m2.43-2.43a9 9 0 0 1 3.316-1.373"
		></path>
	</svg>
);
// Circle icon from https://sidekickicons.com/?iconset=Sidekickicons&code=JSX&icon=circle
export const circleIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		strokeWidth={ 1.5 } stroke="currentColor"
		aria-hidden="true"
		fill="none"
		className="size-6"
	>
		<path strokeLinecap="round"
			strokeLinejoin="round"
			d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"
		></path>
	</svg>
);

// Custom icons (keeping as SVG)
export const todoIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		className="h-6 w-6 stroke-current"
		fill="none"
		aria-hidden="true"
		viewBox="0 0 24 24"
	>
		<circle cx="12" cy="12" r="10" strokeWidth="2" stroke="currentColor"/>
	</svg>
);


export const spinner = (
	<svg
		xmlns="http://www.w3.org/2000/svg/"
		fill="none"
		viewBox="0 0 24 24"
		class="next-steps-spinner nfd-animate-spin nfd-w-8 nfd-h-8"
		role="img"
		aria-hidden="true"
	>
		<circle
			class="nfd-opacity-25"
			cx="12"
			cy="12"
			r="10"
			stroke="currentColor"
			stroke-width="4"></circle>
		<path
			class="nfd-opacity-75"
			fill="currentColor"
			d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
	</svg>
);