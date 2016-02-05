/**
 * Define global vars
 */
var vars = {},
	$modal,
	$sorter,
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

			if (! $modal) {
				$modal = new Garnish.Modal($('.js-modal'));

				selectElement();

				submitSelections();
			} else {
				$modal.show();
			}
		}
	});

	$('.js-close-modal').on('click', function() {
		$modal.hide();
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
			$sorter.removeItems($parent);
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
	var $submit = $('.js-submit');

	new Garnish.Select($('.js-element-body'), $('.js-element-row').filter(':not(.disabled)'), {
		onSelectionChange: function() {
			if ($('.js-element-row.sel').length > 0) {
				$submit.removeClass(isDisabled);
			} else {
				$submit.addClass(isDisabled);
			}
		}
	});
}

/**
 * Submit element row selections
 */
function submitSelections() {
	var $open = $('.js-open-modal');

	$('.js-submit').on('click', function() {
		var $this = $(this),
			$selections = $('.js-element-row.sel');

		if (! $this.hasClass(isDisabled)) {
			$selections.each(function(e, el) {
				var $el = $(el),
					newElement = $el.find($('.js-element'))
						.clone()
						.addClass(isRemovable + ' fresh')
						.prepend('<input name="fields[' + $el.data('name') + '][]" type="hidden" value="' + $el.data('id') + '">' +
							'<a class="delete icon js-remove-element"></a>'
						);

				$('.js-elements').append(newElement);

				values.push($el.data('id'));

				if ($sorter) {
					$sorter.addItems(newElement);
				} else {
					dragSort();
				}
			});

			$selections.removeClass(isSelected).addClass(isDisabled);

			$this.addClass(isDisabled);

			if (values.length >= $open.data('max')) {
				$open.addClass(isDisabled);
			}

			$modal.hide();
		}
	});
}

/**
 * Sort elements
 */
function dragSort() {
	if (values.length > 1) {
		$sorter = new Garnish.DragSort($('.js-element.removable'));
	}
}

/**
 * Search through elements
 */
function searchElements() {
	$('.js-element-search').on('keyup', function() {
		var $this = $(this);

		// Clear the timer if one is set
		if (vars.filterSearchTimer) {
			clearTimeout(vars.filterSearchTimer);
		}

		vars.filterSearchTimer = setTimeout(function() {
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