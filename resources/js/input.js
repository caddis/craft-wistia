var $modal = $('.js-wistia-modal'),
	$garnishModal;

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