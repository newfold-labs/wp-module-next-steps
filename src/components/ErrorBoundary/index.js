import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button } from '@newfold/ui-component-library';

/**
 * Generic Error Boundary Component
 * Catches JavaScript errors anywhere in the child component tree
 */
export class ErrorBoundary extends Component {
	constructor(props) {
		super(props);
		this.state = { 
			hasError: false, 
			error: null, 
			errorInfo: null 
		};
	}

	static getDerivedStateFromError(error) {
		// Update state so the next render will show the fallback UI
		return { hasError: true };
	}

	componentDidCatch(error, errorInfo) {
		// Log error details for debugging
		console.error('ErrorBoundary caught an error:', error, errorInfo);
		
		// Enhanced logging for API errors
		if (error.name?.includes('APIError')) {
			console.error('API Error Details:', {
				endpoint: error.data?.endpoint,
				requestData: error.data?.requestData,
				originalError: error.originalError,
				status: error.originalError?.status
			});
		}
		
		this.setState({
			error: error,
			errorInfo: errorInfo
		});

		// Optional: Send error to logging service
		if (window.NewfoldRuntime?.capabilities?.canLogErrors) {
			this.logErrorToService(error, errorInfo);
		}
	}

	logErrorToService = (error, errorInfo) => {
		// Optional: Send to error tracking service
		try {
			// Example: Send to your error tracking service
			console.warn('Error logged:', {
				error: error.toString(),
				componentStack: errorInfo.componentStack,
				timestamp: new Date().toISOString(),
				userAgent: navigator.userAgent,
				url: window.location.href
			});
		} catch (loggingError) {
			console.error('Failed to log error:', loggingError);
		}
	};

	handleRetry = () => {
		this.setState({ 
			hasError: false, 
			error: null, 
			errorInfo: null 
		});
	};

	render() {
		if (this.state.hasError) {
			// Fallback UI
			const { fallback, showDetails = false } = this.props;
			
			if (fallback) {
				return fallback;
			}

			// Check if this is an API error
			const isApiError = this.state.error?.name?.includes('APIError');
			const errorMessage = isApiError 
				? __('We encountered a connection error. Please check your internet connection and try again.', 'wp-module-next-steps')
				: __('We encountered an unexpected error. Please try refreshing the page or contact support if the problem persists.', 'wp-module-next-steps');

			return (
				<div className="nfd-error-boundary">
					<div className="nfd-error-boundary-content">
						<h3>{ isApiError ? __('Connection Error', 'wp-module-next-steps') : __('Something went wrong', 'wp-module-next-steps') }</h3>
						<p>{ errorMessage }</p>
						
						<div className="nfd-error-boundary-actions">
							<Button
								variant="primary"
								onClick={this.handleRetry}
							>
								{ __('Try Again', 'wp-module-next-steps') }
							</Button>
							
							<Button
								variant="secondary"
								onClick={() => window.location.reload()}
							>
								{ __('Refresh Page', 'wp-module-next-steps') }
							</Button>
						</div>

						{showDetails && this.state.error && (
							<details className="nfd-error-boundary-details">
								<summary>{ __('Technical Details', 'wp-module-next-steps') }</summary>
								<pre className="nfd-error-boundary-stack">
									{this.state.error.toString()}
									{this.state.errorInfo.componentStack}
								</pre>
							</details>
						)}
					</div>
				</div>
			);
		}

		return this.props.children;
	}
}

/**
 * Specialized Error Boundary for Next Steps components
 * Provides context-specific error handling
 */
export class NextStepsErrorBoundary extends Component {
	constructor(props) {
		super(props);
		this.state = { hasError: false };
	}

	static getDerivedStateFromError(error) {
		return { hasError: true };
	}

	componentDidCatch(error, errorInfo) {
		console.error('Next Steps Error:', error, errorInfo);
		
		// Track specific Next Steps errors
		if (window.NewfoldRuntime?.restUrl) {
			this.reportNextStepsError(error, errorInfo);
		}
	}

	reportNextStepsError = async (error, errorInfo) => {
		try {
			// Optional: Report to Next Steps specific endpoint
			console.warn('Next Steps error reported:', {
				module: 'next-steps',
				error: error.message,
				stack: error.stack,
				componentStack: errorInfo.componentStack,
				timestamp: new Date().toISOString()
			});
		} catch (reportError) {
			console.error('Failed to report Next Steps error:', reportError);
		}
	};

	render() {
		if (this.state.hasError) {
			return (
				<div className="nfd-nextsteps-error">
					<div className="nfd-nextsteps-error-content">
						<h3>{ __('Next Steps Unavailable', 'wp-module-next-steps') }</h3>
						<p>
							{ __('The Next Steps module encountered an error and cannot be displayed right now.', 'wp-module-next-steps') }
						</p>
						<Button
							variant="primary"
							onClick={() => window.location.reload()}
						>
							{ __('Refresh Page', 'wp-module-next-steps') }
						</Button>
					</div>
				</div>
			);
		}

		return this.props.children;
	}
}

/**
 * API-specific Error Boundary for handling API failures gracefully
 * Shows more specific messaging for API-related errors
 */
export class APIErrorBoundary extends Component {
	constructor(props) {
		super(props);
		this.state = { hasError: false };
	}

	static getDerivedStateFromError(error) {
		return { hasError: true };
	}

	componentDidCatch(error, errorInfo) {
		console.error('API Error Boundary caught an error:', error, errorInfo);
		
		// Enhanced API error logging
		if (error.name?.includes('APIError')) {
			console.error('API Error Context:', {
				endpoint: error.data?.endpoint,
				method: 'PUT',
				requestData: error.data?.requestData,
				httpStatus: error.originalError?.status,
				timestamp: new Date().toISOString()
			});
		}
	}

	render() {
		if (this.state.hasError) {
			return (
				<div className="nfd-api-error-boundary">
					<div className="nfd-api-error-content">
						<h4>{ __('Connection Issue', 'wp-module-next-steps') }</h4>
						<p>
							{ __('Unable to save your changes right now. Please check your connection and try again.', 'wp-module-next-steps') }
						</p>
						<Button
							variant="secondary"
							onClick={() => window.location.reload()}
						>
							{ __('Refresh Page', 'wp-module-next-steps') }
						</Button>
					</div>
				</div>
			);
		}

		return this.props.children;
	}
}

/**
 * Higher-order component to wrap components with error boundary
 * @param {Component} Component - The component to wrap
 * @param {Object} options - Error boundary options
 * @returns {Component} - Wrapped component with error boundary
 */
export const withErrorBoundary = (WrappedComponent, options = {}) => {
	const WithErrorBoundaryComponent = (props) => {
		return (
			<ErrorBoundary {...options}>
				<WrappedComponent {...props} />
			</ErrorBoundary>
		);
	};

	WithErrorBoundaryComponent.displayName = `withErrorBoundary(${WrappedComponent.displayName || WrappedComponent.name})`;
	
	return WithErrorBoundaryComponent;
};

/**
 * Higher-order component specifically for API error handling
 * @param {Component} Component - The component to wrap
 * @returns {Component} - Wrapped component with API error boundary
 */
export const withAPIErrorBoundary = (WrappedComponent) => {
	const WithAPIErrorBoundaryComponent = (props) => {
		return (
			<APIErrorBoundary>
				<WrappedComponent {...props} />
			</APIErrorBoundary>
		);
	};

	WithAPIErrorBoundaryComponent.displayName = `withAPIErrorBoundary(${WrappedComponent.displayName || WrappedComponent.name})`;
	
	return WithAPIErrorBoundaryComponent;
};