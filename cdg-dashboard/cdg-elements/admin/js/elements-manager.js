(function($) {
'use strict';

class ElementsManager {
	constructor() {
		this.elements = [];
		this.currentElement = null;
		this.isDragging = false;
		this.previewSize = 'desktop';
		
		this.init();
	}

	init() {
		this.initColorPicker();
		this.initDragAndDrop();
		this.initFormHandling();
		this.initPreviewControls();
		this.loadElements();
	}

	initColorPicker() {
		$('.color-picker').wpColorPicker({
			change: (event, ui) => {
				if (this.currentElement) {
					this.updatePreviewElement(this.currentElement.id, {
						color: ui.color.toString()
					});
				}
			}
		});
	}

	initDragAndDrop() {
		$('#preview-canvas').on('mousedown', '.preview-element', (e) => {
			if (e.which !== 1) return; // Left click only
			
			const $element = $(e.currentTarget);
			const elementId = $element.data('id');
			const startX = e.pageX - $element.offset().left;
			const startY = e.pageY - $element.offset().top;
			
			this.isDragging = true;
			this.currentElement = this.elements.find(el => el.id === elementId);
			
			$(document).on('mousemove.elementDrag', (e) => {
				if (!this.isDragging) return;
				
				const canvasOffset = $('#preview-canvas').offset();
				const newX = e.pageX - startX - canvasOffset.left;
				const newY = e.pageY - startY - canvasOffset.top;
				
				this.updatePreviewElement(elementId, {
					position: { x: newX, y: newY }
				});
				
				// Update form inputs
				$('#element-position-x').val(Math.round(newX));
				$('#element-position-y').val(Math.round(newY));
			});
			
			$(document).on('mouseup.elementDrag', () => {
				this.isDragging = false;
				$(document).off('.elementDrag');
				this.saveElement(this.currentElement);
			});
		});
	}

	initFormHandling() {
		$('#cdg-element-form').on('submit', (e) => {
			e.preventDefault();
			this.handleFormSubmit();
		});

		$('#clear-form').on('click', () => {
			this.clearForm();
		});

		// Handle element selection from list
		$('#elements-container').on('click', '.element-item', (e) => {
			const elementId = $(e.currentTarget).data('id');
			this.editElement(elementId);
		});

		// Handle element deletion
		$('#elements-container').on('click', '.delete-element', (e) => {
			e.stopPropagation();
			const elementId = $(e.currentTarget).closest('.element-item').data('id');
			this.deleteElement(elementId);
		});
	}

	initPreviewControls() {
		$('.preview-size').on('click', (e) => {
			const size = $(e.currentTarget).data('size');
			this.setPreviewSize(size);
		});
	}

	loadElements() {
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cdg_get_elements',
				nonce: cdgElements.nonce,
				post_id: cdgElements.postId
			},
			success: (response) => {
				if (response.success) {
					this.elements = response.data;
					this.renderElementsList();
					this.renderPreviewElements();
				}
			}
		});
	}

	handleFormSubmit() {
		const formData = this.getFormData();
		
		// Load font if URL is provided
		const fontUrl = $('#element-font-url').val();
		if (fontUrl) {
			// Create a new stylesheet link
			const linkId = 'cdg-preview-font-' + this.generateUniqueId();
			$(`<link id="${linkId}" href="${fontUrl}" rel="stylesheet">`).appendTo('head');
		}
		
		if (formData.id) {
			// Update existing element
			const elementIndex = this.elements.findIndex(el => el.id === formData.id);
			if (elementIndex !== -1) {
				this.elements[elementIndex] = { ...this.elements[elementIndex], ...formData };
				this.saveElement(this.elements[elementIndex]);
			}
		} else {
			// Create new element
			this.createElement(formData);
		}
	}
	
	generateUniqueId() {
		return Math.random().toString(36).substr(2, 9);
	}

	getFormData() {
		return {
			id: $('#element-id').val(),
			text: $('#element-text').val(),
			font: $('#element-font').val(),
			font_url: $('#element-font-url').val(),
			color: $('#element-color').val(),
			size: $('#element-size').val(),
			rotation: $('#element-rotation').val(),
			position: {
				x: parseInt($('#element-position-x').val()),
				y: parseInt($('#element-position-y').val())
			},
			blur: $('#element-blur').is(':checked')
		};
	}

	createElement(elementData) {
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cdg_create_element',
				nonce: cdgElements.nonce,
				post_id: cdgElements.postId,
				element: elementData
			},
			success: (response) => {
				if (response.success) {
					this.elements.push(response.data);
					this.renderElementsList();
					this.renderPreviewElements();
					this.clearForm();
					this.showNotice('Element created successfully', 'success');
				}
			}
		});
	}

	saveElement(element) {
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'cdg_update_element',
				nonce: cdgElements.nonce,
				element: element
			},
			success: (response) => {
				if (response.success) {
					this.showNotice('Element updated successfully', 'success');
				}
			}
		});
	}

	deleteElement(elementId) {
		if (confirm('Are you sure you want to delete this element?')) {
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: {
					action: 'cdg_delete_element',
					nonce: cdgElements.nonce,
					element_id: elementId
				},
				success: (response) => {
					if (response.success) {
						this.elements = this.elements.filter(el => el.id !== elementId);
						this.renderElementsList();
						this.renderPreviewElements();
						this.clearForm();
						this.showNotice('Element deleted successfully', 'success');
					}
				}
			});
		}
	}

	updatePreviewElement(elementId, updates) {
		const element = this.elements.find(el => el.id === elementId);
		if (!element) return;

		Object.assign(element, updates);
		$(`#preview-element-${elementId}`).css({
			...this.getElementStyles(element),
			...updates
		});
	}

	getElementStyles(element) {
		return {
			left: `${element.position.x}px`,
			top: `${element.position.y}px`,
			fontFamily: element.font,
			color: element.color,
			fontSize: this.getSizeValue(element.size),
			transform: `rotate(${element.rotation}deg)`,
			filter: element.blur ? `blur(${this.getBlurValue(element.size)}px)` : 'none'
		};
	}

	getSizeValue(size) {
		const sizes = {
			'x-small': '12px',
			'small': '16px',
			'medium': '24px',
			'large': '36px'
		};
		return sizes[size] || sizes.medium;
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

	setPreviewSize(size) {
		this.previewSize = size;
		$('.preview-size').removeClass('active');
		$(`.preview-size[data-size="${size}"]`).addClass('active');
		
		$('#preview-canvas')
			.removeClass('size-desktop size-tablet size-mobile')
			.addClass(`size-${size}`);
	}

	renderElementsList() {
		const container = $('#elements-container');
		container.empty();

		this.elements.forEach(element => {
			container.append(`
			  <div class="element-item" data-id="${element.id}">
									<div class="element-item-content">
										<div class="element-preview" style="color: ${element.color}; font-family: ${element.font}">
											${element.text}
										</div>
										<div class="element-details">
											<span class="element-size">${element.size}</span>
											<span class="element-rotation">${element.rotation}°</span>
										</div>
									</div>
									<div class="element-actions">
										<button type="button" class="button edit-element" title="Edit">
											<span class="dashicons dashicons-edit"></span>
										</button>
										<button type="button" class="button delete-element" title="Delete">
											<span class="dashicons dashicons-trash"></span>
										</button>
									</div>
								</div>
							`);
						});
					}
			
					renderPreviewElements() {
						const container = $('#preview-elements');
						container.empty();
			
						this.elements.forEach(element => {
							container.append(`
								<div id="preview-element-${element.id}"
									 class="preview-element"
									 data-id="${element.id}"
									 style="${this.getElementStyleString(element)}">
									${element.text}
								</div>
							`);
						});
					}
			
					getElementStyleString(element) {
						const styles = this.getElementStyles(element);
						return Object.entries(styles)
							.map(([key, value]) => `${this.camelToKebab(key)}: ${value}`)
							.join('; ');
					}
			
					camelToKebab(string) {
						return string.replace(/([a-z0-9])([A-Z])/g, '$1-$2').toLowerCase();
					}
			
					editElement(elementId) {
						const element = this.elements.find(el => el.id === elementId);
						if (!element) return;
			
						this.currentElement = element;
						
						// Populate form
						$('#element-id').val(element.id);
						$('#element-text').val(element.text);
						$('#element-font').val(element.font);
						$('#element-color').wpColorPicker('color', element.color);
						$('#element-size').val(element.size);
						$('#element-rotation').val(element.rotation);
						$('#element-position-x').val(element.position.x);
						$('#element-position-y').val(element.position.y);
						$('#element-blur').prop('checked', element.blur);
			
						// Update UI
						$('#save-element').text('Update Element');
						$('.element-item').removeClass('active');
						$(`.element-item[data-id="${elementId}"]`).addClass('active');
					}
			
					clearForm() {
						this.currentElement = null;
						$('#cdg-element-form')[0].reset();
						$('#element-id').val('');
						$('#element-color').wpColorPicker('color', '#000000');
						$('#save-element').text('Add Element');
						$('.element-item').removeClass('active');
					}
			
					showNotice(message, type = 'success') {
						const notice = $(`
							<div class="notice notice-${type} is-dismissible">
								<p>${message}</p>
								<button type="button" class="notice-dismiss">
									<span class="screen-reader-text">Dismiss this notice.</span>
								</button>
							</div>
						`);
			
						$('.wrap.cdg-elements-manager > h1').after(notice);
			
						// Auto dismiss after 3 seconds
						setTimeout(() => {
							notice.fadeOut(() => notice.remove());
						}, 3000);
			
						// Handle manual dismiss
						notice.find('.notice-dismiss').on('click', () => {
							notice.fadeOut(() => notice.remove());
						});
					}
			
					handleError(error) {
						console.error('CDG Elements Error:', error);
						this.showNotice(
							'An error occurred. Please try again or check the console for details.',
							'error'
						);
					}
				}
			
				// Initialize when document is ready
				$(document).ready(() => {
					window.CDGElementsManager = new ElementsManager();
				});
			
			})(jQuery);