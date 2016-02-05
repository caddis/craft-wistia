/**
 * CP modal
 */
var $modal = $('.js-wistia-modal'),
	$videos = $('.js-wistia-videos'),
	$submit = $('.js-wistia-submit'),
	$row = $('.js-wistia-video-row'),
	$garnishModal,
	isDisabled = 'disabled',
	isSelected = 'sel';

$('.js-wistia-add-video').on('click', function() {
	if (! $garnishModal) {
		$modal.appendTo('body').show();

		$garnishModal = new Garnish.Modal($modal, {
			onShow: function() {
				console.log('hello'); // TODO: A bit of testing
			}
		});
	} else {
		$garnishModal.show();
	}
});

$('.js-wistia-close-modal').on('click', function() {
	$garnishModal.hide();
});

/**
 * Remove selected item
 */
$videos.on('click', '.js-wistia-remove-video', function() {
	$(this).parent().remove();
});

/**
 * Select video row
 */
$row.on('click', function() {
	$(this).toggleClass(isSelected);

	if ($('.js-wistia-video-row.sel').length > 0) {
		$submit.removeClass(isDisabled);
	} else {
		$submit.addClass(isDisabled);
	}
});

/**
 * Submit video selections
 */
$submit.on('click', function() {
	if (! $(this).hasClass(isDisabled)) {
		$garnishModal.hide();

		$('.js-wistia-video-row.sel').each(function(e, el) {
			var $el = $(el),
				id = $el.data('id'),
				title = $el.data('title');

			$videos.append('<div class="element small hasstatus removable" data-id="{{ selectedVideo.hashed_id }}">' +
				'<input name="fields[videos][]" type="hidden" value="' + id + '">' +
					'<a class="delete icon js-wistia-remove-video"></a>' +
					'<span class="status live"></span>' +
					'<div class="label">' +
						'<span class="title">' + title + '</span>' +
					'</div>' +
				'</div>');
		});
	}
});