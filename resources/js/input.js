var $modal = $('.js-wistia-modal'),
	$element = $('.js-wistia-element'),
	$elements = $('.js-wistia-elements'),
	$elementRow = $('.js-wistia-element-row'),
	$submit = $('.js-wistia-submit'),
	$garnishModal,
	isDisabled = 'disabled',
	isSelected = 'sel',
	isRemovable = 'removable';

/**
 * Garnish modal
 */
$('.js-wistia-add-video').on('click', function() {
	var selectedElements = [];

	$('.js-wistia-element.removable').each(function() {
		selectedElements.push($(this).data('id'));
	});

	if (! $garnishModal) {
		$garnishModal = new Garnish.Modal($modal);
	} else {
		$garnishModal.show();
	}

	$elementRow.each(function(e, el) {
		var $el = $(el);

		$el.removeClass(isDisabled);

		$(selectedElements).each(function(i, val) {
			if ($el.data('id') === val) {
				$el.addClass(isDisabled);
			}
		});
	});
});

$('.js-wistia-close-modal').on('click', function() {
	$garnishModal.hide();
});

/**
 * Remove selected item
 */
$elements.on('click', '.js-wistia-remove-video', function() {
	$(this).parent().remove();
});

/**
 * Select video row
 */
$elementRow.on('click', function() {
	var $this = $(this);

	if (! $this.hasClass(isDisabled)) {
		$this.toggleClass(isSelected);
	}

	if ($('.js-wistia-element-row.sel').length > 0) {
		$submit.removeClass(isDisabled);
	} else {
		$submit.addClass(isDisabled);
	}
});

/**
 * Submit video selections
 */
$submit.on('click', function() {
	var $this = $(this),
		$selections = $('.js-wistia-element-row.sel');

	if (! $this.hasClass(isDisabled)) {
		$selections.each(function(e, el) {
			var $el = $(el);

			$elements.append($el.find($element)
				.clone()
				.addClass(isRemovable)
				.prepend('<input name="fields[' + $el.data('name') + '][]" type="hidden" value="' + $el.data('id') + '">' +
					'<a class="delete icon js-wistia-remove-video"></a>'
				)
			);
		});

		$selections.removeClass(isSelected).addClass(isDisabled);

		$this.addClass(isDisabled);

		$garnishModal.hide();
	}
});