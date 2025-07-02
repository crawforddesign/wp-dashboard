(function($) {
	'use strict';

	// Store elements data
	let elements = [];
	let activeElement = null;

	// Initialize admin interface
	function init() {
		initColorPickers();
		initTabs();
		initDragAndDrop();
		loadElements();
		initElementForm();
	}

	// Initialize color pickers
	function initColorPickers() {
		$('.color-picker').wpColorPicker({
			change: function(event, ui) {
				if (activeElement) {
					activeElement.color = ui.color.toString();
					updatePreview();
				}
			}
		});
	}

	// Initialize tabs
	function initTabs() {
		$('.nav-tab').on('click', function(e) {
			e.preventDefault();
			$('.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			
			const target = $(this).attr('href').substring(1);
			$('.tab-content').removeClass('active');
			$(`#${target}`).addClass('active');
		});
	}

	// Initialize drag and drop
	function initDragAndDrop() {
		$('#preview-canvas').on('mousedown', '.draggable-element', function(e) {
			const $element = $(this);
			const startX = e.pageX - $element.offset().left;
			const startY = e.pageY - $element.offset().top;

			$(document).on('mousemove.dragElement', function(e) {
				const newX = e.pageX - startX - $('#preview-canvas').offset().left;
				const newY = e.pageY - startY - $('#preview-canvas').offset().top;
				
				$element.css({
					left: newX + 'px',
					top: newY + 'px'
				});

				// Update position inputs
				$('#element-x').val(Math.round(newX));
				$('#element-y').val(Math.round(newY));
			});

			$(document).on('mouseup.dragElement', function() {
				$(document).off('.dragElement');
			});
		});
	}

	// Load existing elements
	function loadElements() {
		$.ajax({
			url: cdgElements.ajaxurl,
			type: 'POST',
			data: {
				action: 'get_elements',
				nonce: cdgElements.nonce
			},
			success: function(response) {
				if (response.success) {
					elements = response.data;
					updatePreview();
					updateElementsList();
				}
			}
		});
	}

	// Initialize element form
	function initElementForm() {
		$('#add-element-form').on('submit', function(e) {
			e.preventDefault();
			
			const element = {
				text: $('#element-text').val(),
				font: $('#element-font').val(),
				color: $('#element-color').val(),
				size: $('#element-size').val(),
				rotation: $('#element-rotation').val(),
				position: {
					x: parseInt($('#element-x').val()),
					y: parseInt($('#element-y').val())
				}
			};

			elements.push(element);
			updatePreview();
			updateElementsList();
			saveElements();
			
			// Reset form
			this.reset();
		});
	}

	// Update preview canvas
	function updatePreview() {
		const $canvas = $('#preview-canvas');
		$canvas.find('.draggable-element').remove();

		elements.forEach((element, index) => {
			const $element = $('<div>', {
				class: 'draggable-element',
				text: element.text,
				css: {
					left: element.position.x + 'px',
					top: element.position.y + 'px',
					fontFamily: element.font,
					color: element.color,
					fontSize: getSizeValue(element.size),
					transform: `rotate(${element.rotation}deg)`,
					filter: `blur(${getBlurValue(element.size)}px)`
				},
				'data-index': index
			});

			$canvas.append($element);
		});
	}

	// Update elements list
	function updateElementsList() {
		const $container = $('#elements-container');
		$container.empty();

		elements.forEach((element, index) => {
			const $element = $(`
				<div class="element-item" data-index="${index}">
					<span class="element-text">${element.text}</span>
					<div class="element-actions">
						<button class="button edit-element">Edit</button>
						<button class="button button-link-delete delete-element">Delete</button>
					</div>
				</div>
			`);

			$container.append($element);
		});
	}

	// Helper functions
	function getSizeValue(size) {
		const sizes = {
			'x-small': '12px',
			'small': '16px',
			'medium': '24px',
			'large': '36px'
		};
		return sizes[size] || sizes.medium;
	}

	function getBlurValue(size) {
		const blurs = {
			'x-small': 3,
			'small': 2,
			'medium': 1,
			'large': 0
		};
		return blurs[size] || 0;
	}

	// Save elements to server
	function saveElements() {
		$.ajax({
			url: cdgElements.ajaxurl,
			type: 'POST',
			data: {
				action: 'save_elements',
				nonce: cdgElements.nonce,
				elements: elements
			},
			success: function(response) {
				if (response.success) {
					// Show success message
					showNotice('Elements saved successfully', 'success');
				} else {
					// Show error message
					showNotice('Error saving elements', 'error');
				}
			}
		});
	}

	// Show admin notice
	function showNotice(message, type) {
		const $notice = $(`<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`);
		$('.wrap.cdg-elements-admin > h1').after($notice);
		
		// Auto dismiss after 3 seconds
		setTimeout(() => {
			$notice.fadeOut(() => $notice.remove());
		}, 3000);
	}

	// Initialize on document ready
	$(document).ready(init);

})(jQuery);