(function($){

Wistia = {};

Wistia.VideosIndex = Craft.BaseElementIndex.extend({
	init: function(elementType, $container, settings) {
		this.base(elementType, $container, settings);
	}
});

Wistia.VideosElementSelectorInput = Craft.BaseElementSelectInput.extend({
	init: function() {
		this.settings = arguments;

		this.base.apply(this, this.settings);
	},

	removeElements: function($elements)
	{
		if (this.settings.selectable)
		{
			this.elementSelect.removeItems($elements);
		}

		// Disable the hidden input in case the form is submitted before this element gets removed from the DOM
		$elements.children('input').prop('disabled', true);

		this.$elements = this.$elements.not($elements);
		this.updateAddElementsBtn();

		this.onRemoveElements();
	},

	createModal: function() {
		return new Wistia.Modal(this, this.settings);
	},

	updateSelectableElements: function() {
		var scope = this,
			isDisabled = 'disabled';

		scope.modal.$body.find('.data tbody tr').each(function(e, el) {
			var $el = $(el);

			$el.removeClass(isDisabled);

			Wistia.scope.getSelectedElementIds().each(function(i, val) {
				if ($el.data('id') === val) {
					$el.addClass(isDisabled);
				}
			});
		});
	}
});

Wistia.Modal = Craft.BaseElementSelectorModal.extend({
	init: function(ElementSelectInput, settings) {
		this.ElementSelectInput = ElementSelectInput;
		this.settings = settings;

		this.base(null, settings);
	},

	onFadeIn: function() {
		if (this.videoDataLoaded) {
			this.elementSelector.addItems(this.$elementRow.filter(':not(.disabled)'));
		}

		this.base();
	},

	updateSelectBtnState: function() {
		if (this.$selectBtn) {
			if (this.elementSelector.getTotalSelected()) {
				this.enableSelectBtn();
			} else {
				this.disableSelectBtn();
			}
		}
	},

	selectElements: function() {
		var scope = this,
			isDisabled = 'disabled';

		if (! this.$selectBtn.hasClass(isDisabled)) {
			this.elementSelector.getSelectedItems().each($.proxy(function(e, el) {
				var $el = $(el),
					newElement = $el.find($('.element'))
						.clone()
						.addClass('removable')
						.prepend('<input name="' + this.settings.name + '[]" type="hidden" value="' + $el.data('id') + '">' +
							'<a class="delete icon" title="'+Craft.t('Remove')+'"></a>');

				// Add new elements to selection list
				this.ElementSelectInput.appendElement(newElement);

				// Update elements object
				this.ElementSelectInput.addElements(newElement);

				// this.updateAddBtnState();
			}, this));

			this.hide();

			// Clear out selection
			this.elementSelector.deselectAll();
			this.elementSelector.removeAllItems();
		}
	},

	_createElementIndex: function() {
		// Get the modal body HTML based on the settings
		var data = {
			projectIds: this.settings.projectIds
		};

		Craft.postActionRequest(Craft.getActionUrl('wistia/videos/getModal'), data, $.proxy(function(response, textStatus) {
			if (textStatus == 'success') {
				// Add video data to modal
				this.$body.html(response);

				this.elementIndex = new Wistia.VideosIndex(null, this.$container, null);

				console.log(this.elementIndex);

				this._createElementSelector();

				this._createElementSearch();

				this.videoDataLoaded = true;
			}
		}, this));
	},

	_createElementSelector: function() {
		if (! this.elementSelectorCreated) {
			var $container = this.$body.find('.data tbody');
				$elements = this.$body.find('.data tbody tr');

			this.$elementRow = $elements;

			this.elementSelector = new Garnish.Select($container, $elements.filter(':not(.disabled)'), {
					filter: ':not(.disabled)',
					onSelectionChange: $.proxy(this, 'updateSelectBtnState')
				});
		}
	},

	_createElementSearch: function() {
		var scope = this;

		scope.$body.find('.search input').on('keyup', function() {
			var $this = $(this);

			// Clear the timer if one is set
			if (scope.filterSearchTimer) {
				clearTimeout(scope.filterSearchTimer);
			}

			scope.filterSearchTimer = setTimeout(function() {
				// Retrieve the input field text
				var filter = $this.val();

				// Loop through the rows
				scope.$elementRow.each(function(e, el) {
					var $el = $(el);

					// If the row does not contain the text phrase hide it
					if ($el.data('title').search(new RegExp(filter, 'i')) < 0) {
						$el.hide();
					} else {
						// Show the row if the phrase matches
						$el.show();
					}
				});
			}, 300);
		});
	}
});

})(jQuery);