(function($) {
	'use strict';

	class ElementsManager {
		constructor() {
			this.elements = [];
			this.observer = null;
			this.viewportWidth = window.innerWidth;
			this.resizeTimeout = null;
			
			this.init();
		}

		init() {
			this.initIntersectionObserver();
			this.handleResize();
			this.bindEvents();
		}

		initIntersectionObserver() {
			this.observer = new IntersectionObserver(
				(entries) => this.handleIntersection(entries),
				{
					threshold: [0, 0.1, 0.5, 1],
					rootMargin: '50px'
				}
			);

			$('.cdg-element').each((_, element) => {
				this.observer.observe(element);
			});
		}

		handleIntersection(entries) {
			entries.forEach(entry => {
				const element = entry.target;
				
				if (entry.isIntersecting) {
					$(element).addClass('is-visible');
				} else {
					// Only hide if element is completely out of view
					if (entry.intersectionRatio === 0) {
						$(element).removeClass('is-visible');
					}
				}
			});
		}

		handleResize() {
			// Debounce resize events
			$(window).on('resize', () => {
				if (this.resizeTimeout) {
					clearTimeout(this.resizeTimeout);
				}

				this.resizeTimeout = setTimeout(() => {
					const newWidth = window.innerWidth;
					if (newWidth !== this.viewportWidth) {
						this.viewportWidth = newWidth;
						this.adjustElementPositions();
					}
				}, 150);
			});
		}

		adjustElementPositions() {
			$('.cdg-element').each((_, element) => {
				const $element = $(element);
				const position = this.calculateResponsivePosition($element);
				
				$element.css({
					left: position.x + 'px',
					top: position.y + 'px'
				});
			});
		}

		calculateResponsivePosition($element) {
			const originalX = parseInt($element.css('left'));
			const originalY = parseInt($element.css('top'));
			
			// Calculate positions based on viewport width
			const viewportRatio = this.viewportWidth / 1920; // Base width
			
			return {
				x: Math.round(originalX * viewportRatio),
				y: Math.round(originalY * viewportRatio)
			};
		}

		bindEvents() {
			// Pause animations when page is not visible
			document.addEventListener('visibilitychange', () => {
				const container = $('#cdg-elements-container');
				if (document.hidden) {
					container.css('animation-play-state', 'paused');
				} else {
					container.css('animation-play-state', 'running');
				}
			});

			// Handle page transitions
			$(window).on('beforeunload', () => {
				$('#cdg-elements-container').css('opacity', 0);
			});
		}
	}

	// Initialize when document is ready
	$(document).ready(() => {
		window.cdgElementsManager = new ElementsManager();
	});

})(jQuery);