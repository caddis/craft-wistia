/**
 * Define global scope
 */
var scope = {},
	isDisabled = 'disabled',
	isSelected = 'sel',
	isRemovable = 'removable',
	values = $('.js-elements-value').data('value');

/**
 * Update selectable elements in the modal
 */
function updateSelections() {
	$('.js-element-row').each(function(e, el) {
		var $el = $(el);

		$el.removeClass(isDisabled);

		$(values).each(function(i, val) {
			if ($el.data('id') === val) {
				$el.addClass(isDisabled);
			}
		});
	});
}

/**
 * Garnish modal
 */
function modal() {
	$('.js-open-modal').on('click', function() {
		if (! $(this).hasClass(isDisabled)) {
			updateSelections();

			if (! scope.modal) {
				scope.modal = new Garnish.Modal($('.js-modal'), {
					onShow: function() {
						updateSelect();
					}
				});

				selectElement();

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
 * Remove selected element
 */
function removeElement() {
	var $open = $('.js-open-modal');

	$('.js-elements').on('click', '.js-remove-element', function() {
		$parent = $(this).parent();

		$parent.remove();

		values.splice(values.indexOf($parent.data('id')), 1);

		if (values.length >= 1) {
			scope.sorter.removeItems($parent);
		}

		if (values.length < $open.data('max')) {
			$open.removeClass(isDisabled);
		}
	});
}

/**
 * Select element row
 */
function selectElement() {
	var $row = $('.js-element-row');

	$row.on('click', function() {
		var $el = $(this);

		if (! $el.hasClass(isDisabled)) {
			if (! $el.hasClass(isSelected)) {
				$row.removeClass(isSelected);

				$el.addClass(isSelected);
			} else {
				$el.removeClass(isSelected);
			}
		} else {
			$row.removeClass(isSelected);
		}

		updateSelect();
	});
}

/**
 * Submit element row selections
 */
function submitSelections() {
	var $open = $('.js-open-modal');

	$('.js-submit').on('click', function() {
		var $this = $(this),
			$row = $('.js-element-row');

		if (! $this.hasClass(isDisabled)) {
			$row.filter('.sel').each(function(e, el) {
				var $el = $(el),
					newElement = $el.find($('.js-element'))
						.clone()
						.addClass(isRemovable + ' fresh')
						.prepend('<input name="fields[' + $el.data('name') + '][]" type="hidden" value="' + $el.data('id') + '">' +
							'<a class="delete icon js-remove-element"></a>'
						);

				// Add new elements to selection list
				$('.js-elements').append(newElement);

				// Push newly added elements into main values array
				values.push($el.data('id'));

				// Update drag and sort
				if (scope.sorter) {
					scope.sorter.addItems(newElement);
				} else {
					dragSort();
				}
			});

			// Hide add more elements button if element max is met
			if (values.length >= $open.data('max')) {
				$open.addClass(isDisabled);
			}

			// Hide the modal
			scope.modal.hide();

			// Add disabled class to select button
			$this.addClass(isDisabled);

			// Remove selected class from all rows
			$row.removeClass(isSelected);
		}
	});
}

/**
 * Update select button based on select elements
 */
function updateSelect() {
	$submit = $('.js-submit');

	if ($('.js-element-row').filter('.sel').length > 0) {
		$submit.removeClass(isDisabled);
	} else {
		$submit.addClass(isDisabled);
	}
}

/**
 * Sort elements
 */
function dragSort() {
	if (values.length > 1) {
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

			// Loop through the labels
			$('.js-element-row').each(function() {
				var $this = $(this);

				// If the label does not contain the text phrase hide it
				if ($this.data('title').search(new RegExp(filter, 'i')) < 0) {
					$this.hide();
				} else {
					// Show the label if the phrase matches
					$this.show();
				}
			});
		}, 300);
	});
}


/**
 * Instantiate functions
 */
modal();
removeElement();
dragSort();
searchElements();