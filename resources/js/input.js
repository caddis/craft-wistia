(function() {
	var scope = {
		elements: {},
		selector: {},
		values: [],
		isDisabled: 'disabled',
		isSelected: 'sel'
	};

	/**
	 * Init element selector
	 */
	function init() {
		var $elementSelect = $('.js-element-select');

		scope.elements = new Craft.BaseElementSelectInput({
			id: $elementSelect.attr('id'),
			limit: $elementSelect.data('max'),
			onRemoveElements: function() {
				updateValues();
				updateAddBtn();
			}
		});

		updateValues();
	}

	/**
	 * Update values
	 */
	function updateValues() {
		scope.values = scope.elements.getSelectedElementIds(scope.elements);
	}

	function updateAddBtn() {
		var $add = $('.js-add');

		// Hide add more elements button if element max is met
		if (scope.values.length >= $('.js-element-select').data('max')) {
			$add.addClass(scope.isDisabled);
		} else {
			$add.removeClass(scope.isDisabled);
		}
	}

	/**
	 * Open and close modal
	 */
	function modal() {
		$('.js-add').on('click', function() {
			if (! $(this).hasClass(scope.isDisabled)) {
				if (! scope.modal) {
					scope.modal = new Garnish.Modal($('.js-modal'), {
						onShow: function() {
							updateSelectBtn();
							updateSelectableElements();
						}
					});

					scope.selector = new Garnish.Select($('.js-element-body'), $('.js-element-row').filter(':not(.disabled)'), {
							onSelectionChange: function() {
								updateSelectBtn();
							}
						});

					submitSelections();
				} else {
					scope.modal.show();
				}
			}
		});

		$('.js-close-modal').on('click', function() {
			scope.modal.hide();
		});
	}

	/**
	 * Add selected modal elements to final list
	 */
	function submitSelections() {
		$('.js-submit').on('click', function() {
			var $this = $(this),
				$selector = scope.selector,
				$items = $selector.getSelectedItems();

			if (! $this.hasClass(scope.isDisabled)) {
				$items.each(function(e, el) {
					var $el = $(el),
						newElement = $el.find($('.element'))
							.clone()
							.addClass('removable')
							.prepend('<input name="fields[' + $el.data('name') + '][]" type="hidden" value="' + $el.data('id') + '">' +
								'<a class="delete icon" title="'+Craft.t('Remove')+'"></a>');

					// Add new elements to selection list
					scope.elements.appendElement(newElement);

					// Update elements object
					scope.elements.addElements(newElement);

					updateValues();
					updateAddBtn();
				});

				// Hide the modal
				scope.modal.hide();

				// Add disabled class to select button
				$this.addClass(scope.isDisabled);

				// Remove selected class from all items
				$selector.deselectAll($items);
			}
		});
	}

	/**
	 * Update which modal elements are selectable
	 */
	function updateSelectableElements() {
		$('.js-element-row').each(function(e, el) {
			var $el = $(el);

			$el.removeClass(scope.isDisabled);

			$(scope.values).each(function(i, val) {
				if ($el.data('id') === val) {
					$el.addClass(scope.isDisabled);
				}
			});
		});
	}

	/**
	 * Update select button based on which modal elements are selected
	 */
	function updateSelectBtn() {
		$submit = $('.js-submit');

		if ($('.js-element-row').filter('.sel').length > 0) {
			$submit.removeClass(scope.isDisabled);
		} else {
			$submit.addClass(scope.isDisabled);
		}
	}

	/**
	 * Search through elements
	 */
	function searchElements() {
		$('.js-element-search').on('keyup', function() {
			var $this = $(this);

			// Clear the timer if one is set
			if (scope.filterSearchTimer) {
				clearTimeout(scope.filterSearchTimer);
			}

			scope.filterSearchTimer = setTimeout(function() {
				// Retrieve the input field text
				var filter = $this.val();

				// Loop through the rows
				$('.js-element-row').each(function() {
					var $this = $(this);

					// If the row does not contain the text phrase hide it
					if ($this.data('title').search(new RegExp(filter, 'i')) < 0) {
						$this.hide();
					} else {
						// Show the row if the phrase matches
						$this.show();
					}
				});
			}, 300);
		});
	}

	init();
	modal();
	searchElements();
})();