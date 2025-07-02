(function($) {
	'use strict';

	class PreviewManager {
		constructor() {
			this.modal = $('#cdg-preview-modal');
			this.elements = [];
			this.scale = 'desktop';
			this.init();
		}

		init() {
			this.bindEvents();
			this.initScaleControls();
		}

		bindEvents() {
			$('.preview-element').on('click', () => this.showPreview());
			$('.cdg-modal-close').on('click', () => this.hidePreview());
			$(document).on('keyup', (e) => {
				if (e.key === 'Escape') this.hidePreview();
			});
		}

		initScaleControls() {
			$('.preview-scale').on('click', (e) => {
				const scale = $(e.target).data('scale');
				this.setScale(scale);
			});
		}

		showPreview() {
			this.modal.addClass('active');
			this.loadPreviewContent();
		}

		hidePreview() {
			this.modal.removeClass('active');
		}

		setScale(scale) {
			this.scale = scale;
			const container = $('#preview-frame-container');
			container.removeClass('scale-desktop scale-tablet scale-mobile')
					.addClass(`scale-${scale}`);
		}

		loadPreviewContent() {
			const container = $('#preview-frame-container');
			container.html(CDG_Elements_Preview.get_preview_frame_content());
			this.renderElements();
		}

		renderElements() {
			const container = $('#cdg-elements-container');
			container.empty();

			this.elements.forEach(element => {
				const $el = $('<div>', {
					class: `cdg-element ${element.size}`,
					text: element.text,
					css: {
						left: element.position.x + 'px',
						top: element.position.y + 'px',
						fontFamily: element.font,
						color: element.color,
						transform: `rotate(${element.rotation}deg)`,
						filter: `blur(${this.getBlurValue(element.size)}px)`
					}
				});

				container.append($el);
			});
		}

		getBlurValue(size) {
			const blurs = {
				'x-small': 3,
				'small': 2,
				'medium': 1,
				'large': 0
			};
			return blurs[size] || 0;
		}

		updateElements(elements) {
			this.elements = elements;
			if (this.modal.hasClass('active')) {
				this.renderElements();
			}
		}
	}

	// Initialize preview manager when document is ready
	$(document).ready(() => {
		window.cdgPreviewManager = new PreviewManager();
	});

})(jQuery);