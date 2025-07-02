(function($) {
	'use strict';

	// Initialize elements visibility based on scroll position
	function initElementsVisibility() {
		const container = $('#cdg-elements-container');
		if (!container.length) return;

		const elements = container.find('.cdg-element');
		const observer = new IntersectionObserver(
			(entries) => {
				entries.forEach(entry => {
					const element = entry.target;
					if (entry.isIntersecting) {
						$(element).addClass('visible');
					} else {
						$(element).removeClass('visible');
					}
				});
			},
			{
				threshold: 0.1
			}
		);

		elements.each(function() {
			observer.observe(this);
		});
	}

	// Initialize when document is ready
	$(document).ready(function() {
		initElementsVisibility();
	});

})(jQuery);