/**
 * Define global vars (yucky)
 */
var $element = $('.js-element'),
	$elements = $('.js-elements'),
	$elementRow = $('.js-element-row'),
	$submit = $('.js-submit'),
	$garnishModal,
	$sorter,
	isDisabled = 'disabled',
	isSelected = 'sel',
	isRemovable = 'removable';

/**
 * Update selectable elements in the modal
 */
function updateSelections() {
	var selectedElements = [];

	$('.js-element.removable').each(function() {
		selectedElements.push($(this).data('id'));
	});

	$elementRow.each(function(e, el) {
		var $el = $(el);

		$el.removeClass(isDisabled);

		$(selectedElements).each(function(i, val) {
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

			if (! $garnishModal) {
				$garnishModal = new Garnish.Modal($('.js-modal'));

				selectElement();
				submitSelections();
			} else {
				$garnishModal.show();
			}
		}
	});

	$('.js-close-modal').on('click', function() {
		$garnishModal.hide();
	});
}

/**
 * Remove selected element
 */
function removeElement() {
	$elements.on('click', '.js-remove-element', function() {
		$parent = $(this).parent();

		$parent.remove();

		$sorter.removeItems($parent);
	});
}

/**
 * Select element row
 */
function selectElement() {
	var rowSelect = new Garnish.Select($('.js-element-body'), $elementRow.not('.disabled'), {
			multi: true,
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
	$submit.on('click', function() {
		var $this = $(this),
			$selections = $('.js-element-row.sel');

		if (! $this.hasClass(isDisabled)) {
			$selections.each(function(e, el) {
				var $el = $(el),
					newElement = $el.find($element)
						.clone()
						.addClass(isRemovable + ' fresh')
						.prepend('<input name="fields[' + $el.data('name') + '][]" type="hidden" value="' + $el.data('id') + '">' +
							'<a class="delete icon js-remove-element"></a>'
						);

				$elements.append(newElement);

				$sorter.addItems(newElement);
			});

			$selections.removeClass(isSelected).addClass(isDisabled);

			$this.addClass(isDisabled);

			$garnishModal.hide();
		}
	});
}

/**
 * Sort elements
 */
function dragSort() {
	$sorter = new Garnish.DragSort($('.js-element.removable'));
}

/**
 * Instantiate functions
 */
modal();
removeElement();
dragSort();