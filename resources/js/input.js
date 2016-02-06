(function() {
	var scope = {
		isDisabled: 'disabled',
		isSelected: 'sel',
		values: $('.js-elements-value').data('value')
	};

	/**
	 * Open and close modal
	 */
	function modal() {
		$('.js-open-modal').on('click', function() {
			if (! $(this).hasClass(scope.isDisabled)) {
				if (! scope.modal) {
					scope.modal = new Garnish.Modal($('.js-modal'), {
						onShow: function() {
							updateSelectBtn();
							updateSelectableElements();
						}
					});

					selectModalElement();

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
	 * Remove selected element from final list
	 */
	function removeElement() {
		var $open = $('.js-open-modal');

		$('.js-elements').on('click', '.js-remove-element', function() {
			$parent = $(this).parent();

			$parent.remove();

			scope.values.splice(scope.values.indexOf($parent.data('id')), 1);

			if (scope.values.length >= 1) {
				scope.sorter.removeItems($parent);
			}

			if (scope.values.length < $open.data('max')) {
				$open.removeClass(scope.isDisabled);
			}
		});
	}

	/**
	 * Select modal element row
	 */
	function selectModalElement() {
		var $row = $('.js-element-row');

		$row.on('click', function() {
			var $el = $(this);

			if (! $el.hasClass(scope.isDisabled)) {
				if (! $el.hasClass(scope.isSelected)) {
					$row.removeClass(scope.isSelected);

					$el.addClass(scope.isSelected);
				} else {
					$el.removeClass(scope.isSelected);
				}
			} else {
				$row.removeClass(scope.isSelected);
			}

			updateSelectBtn();
		});
	}

	/**
	 * Add selected modal elements to final list
	 */
	function submitSelections() {
		var $open = $('.js-open-modal');

		$('.js-submit').on('click', function() {
			var $this = $(this),
				$row = $('.js-element-row');

			if (! $this.hasClass(scope.isDisabled)) {
				$row.filter('.sel').each(function(e, el) {
					var $el = $(el),
						newElement = $el.find($('.js-element'))
							.clone()
							.addClass('removable fresh')
							.prepend('<input name="fields[' + $el.data('name') + '][]" type="hidden" value="' + $el.data('id') + '">' +
								'<a class="delete icon js-remove-element"></a>');

					// Add new elements to selection list
					$('.js-elements').append(newElement);

					// Push newly added elements into main values array
					scope.values.push($el.data('id'));

					// Update drag and sort
					if (scope.sorter) {
						scope.sorter.addItems(newElement);
					} else {
						dragSort();
					}
				});

				// Hide add more elements button if element max is met
				if (scope.values.length >= $open.data('max')) {
					$open.addClass(scope.isDisabled);
				}

				// Hide the modal
				scope.modal.hide();

				// Add disabled class to select button
				$this.addClass(scope.isDisabled);

				// Remove selected class from all rows
				$row.removeClass(scope.isSelected);
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
	 * Drag and sort elements
	 */
	function dragSort() {
		if (scope.values.length > 1) {
			scope.sorter = new Garnish.DragSort($('.js-element.removable'));
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

	modal();
	removeElement();
	dragSort();
	searchElements();
})();