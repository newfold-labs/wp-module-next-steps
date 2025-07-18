import { 
	ArrowRightIcon,
	ChevronUpIcon,
	PlusCircleIcon,
	MinusCircleIcon,
	EyeSlashIcon,
	EyeIcon
} from '@heroicons/react/24/outline';
import { CheckCircleIcon } from '@heroicons/react/24/solid';

// Using Heroicons components
export const chevron = <ChevronUpIcon className="size-6" />;
export const openCircle = <PlusCircleIcon className="size-6" />;
export const closeCircle = <MinusCircleIcon className="size-6" />;
export const hideIcon = <EyeSlashIcon className="size-6" />;
export const showIcon = <EyeIcon className="size-6" />;
export const doneIcon = <CheckCircleIcon className="size-6" />;
export const goIcon = <ArrowRightIcon className="size-6" />;

// Custom icons (keeping as SVG)
export const todoIcon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		className="h-6 w-6 stroke-current"
		fill="none"
		viewBox="0 0 24 24"
	>
		<circle cx="12" cy="12" r="10" strokeWidth="2" stroke="currentColor" />
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