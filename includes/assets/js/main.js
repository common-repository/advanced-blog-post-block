/* eslint-disable no-undef */
(function () {
	'use strict';
	function animateOnIntersection(entries, observer) {
		entries.forEach((entry) => {
			if (entry.isIntersecting) {
				entry.target.classList.add('animate');
				observer.unobserve(entry.target);
			}
		});
	}

	const observer = new IntersectionObserver(animateOnIntersection, {
		threshold: 0,
	});

	function startAnimationObserver() {
		const animationWrapper = document.querySelectorAll(
			'.bwdabpb-item-wrapper'
		);
		animationWrapper.forEach((animationItem) => {
			observer.observe(animationItem);
		});
	}

	// Use a MutationObserver to detect when the elements appear in the DOM
	const mutationObserver = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			if (mutation.addedNodes && mutation.addedNodes.length > 0) {
				startAnimationObserver();
			}
		});
	});

	// Start observing changes in the DOM
	mutationObserver.observe(document.body, { childList: true, subtree: true });

	// Start the observer as soon as the DOM is ready
	if (document.readyState === 'complete') {
		startAnimationObserver();
	} else {
		window.addEventListener('load', startAnimationObserver);
	}


})();


