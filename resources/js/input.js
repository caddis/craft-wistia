(function($){

Wistia = {};

Wistia.Videos = Garnish.Base.extend({
	/**
	 * Init element selector
	 */
	init: function(settings) {
		var scope = this;

		scope.settings = settings;
		scope.settings.max = parseInt(scope.settings.max);
		scope.elementSelectContainer = $('#' + scope.settings.id);
		scope.$addElementBtn = scope.elementSelectContainer.find('.add');

		scope.elements = new Craft.BaseElementSelectInput({
			id: scope.settings.id,
			limit: scope.settings.max,
			onRemoveElements: function() {
				scope.updateAddBtnState();
			}
		});

		scope.initAddBtn();
		scope.updateAddBtnState();
		scope.toggleModal();
	},

	/**
	 * Add video button
	 */
	initAddBtn: function() {
		if (this.$addElementBtn && this.settings.max === 1) {
			this.$addElementBtn
				.css('position', 'absolute')
				.css('top', 0)
				.css(Craft.left, 0);
		}
	},

	updateAddBtnState: function() {
		if (this.elements.canAddMoreElements()) {
			this.disabledAddBtn();
		} else {
			this.enableAddBtn();
		}
	},

	disabledAddBtn: function() {
		this.$addElementBtn.removeClass('disabled');

		if (this.settings.max === 1) {
			if (this.elements._initialized) {
				this.$addElementBtn.velocity('fadeIn', Craft.BaseElementSelectInput.REMOVE_FX_DURATION);
			} else {
				this.$addElementBtn.show();
			}
		}
	},

	enableAddBtn: function() {
		this.$addElementBtn.addClass('disabled');

		if (this.settings.max === 1) {
			if (this.elements._initialized) {
				this.$addElementBtn.velocity('fadeOut', Craft.BaseElementSelectInput.ADD_FX_DURATION);
			} else {
				this.$addElementBtn.hide();
			}
		}
	},

	/**
	 * Video modal
	 */
	toggleModal: function() {
		var scope = this;

		scope.$addElementBtn.on('click', function() {
			if (! $(this).hasClass('disabled')) {
				if (! scope.modal) {
					scope.getModalData();
				} else {
					scope.updateSelectableElements();

					scope.selector.addItems(scope.$elementRow.filter(':not(.disabled)'));

					scope.modal.show();
				}
			}
		});
	},

	getModalData: function() {
		var	scope = this;

		$.get(Craft.getActionUrl('wistia/videos/getModal', {projectIds: scope.settings.projectIds}), function(data) {
			scope.initModal(data);
		});
	},

	initModal: function(data) {
		this.modal = new Garnish.Modal($(data)[0]);

		// Define modal structure
		this.$modalCancel = this.modal.$container.find('.btn:not(.submit)');
		this.$modalSubmit = this.modal.$container.find('.btn.submit');
		this.$elementRowContainer = this.modal.$container.find('.data tbody');
		this.$elementRow = this.$elementRowContainer.children();

		this.modal.addListener(this.$modalCancel, 'click', 'hide');

		this.updateSelectableElements();

		this.initModalSelector();

		this.updateSelectBtn();

		this.searchElements();

		this.submitSelections();
	},

	/**
	 * Init modal selector
	 */
	initModalSelector: function() {
		var	scope = this;

		scope.selector = new Garnish.Select(scope.$elementRowContainer,
			scope.$elementRow.filter(':not(.disabled)'), {
				onSelectionChange: function() {
					scope.updateSelectBtn();
				}
			});
	},

	/**
	 * Update which modal elements are selectable
	 */
	updateSelectableElements: function() {
		var scope = this,
			isDisabled = 'disabled';

		scope.$elementRow.each(function(e, el) {
			var $el = $(el);

			$el.removeClass(isDisabled);

			$(scope.elements.getSelectedElementIds()).each(function(i, val) {
				if ($el.data('id') === val) {
					$el.addClass(isDisabled);
				}
			});
		});
	},

	/**
	 * Update select button based on which modal elements are selected
	 */
	updateSelectBtn: function() {
		var	isDisabled = 'disabled';

		if (this.selector.getTotalSelected()) {
			this.$modalSubmit.removeClass(isDisabled);
		} else {
			this.$modalSubmit.addClass(isDisabled);
		}
	},

	/**
	 * Search through elements
	 */
	searchElements: function() {
		var scope = this;

		scope.modal.$container.find('.search input').on('keyup', function() {
			var $this = $(this);

			// Clear the timer if one is set
			if (scope.filterSearchTimer) {
				clearTimeout(scope.filterSearchTimer);
			}

			scope.filterSearchTimer = setTimeout(function() {
				// Retrieve the input field text
				var filter = $this.val();

				// Loop through the rows
				scope.$elementRow.each(function() {
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
	},

	/**
	 * Add selected modal elements to final list
	 */
	submitSelections: function() {
		var scope = this,
			isDisabled = 'disabled';

		scope.$modalSubmit.on('click', function() {
			var $this = $(this);

			if (! $this.hasClass(isDisabled)) {
				scope.selector.getSelectedItems().each(function(e, el) {
					var $el = $(el),
						newElement = $el.find($('.element'))
							.clone()
							.addClass('removable')
							.prepend('<input name="' + scope.settings.name + '[]" type="hidden" value="' + $el.data('id') + '">' +
								'<a class="delete icon" title="'+Craft.t('Remove')+'"></a>');

					// Add new elements to selection list
					scope.elements.appendElement(newElement);

					// Update elements object
					scope.elements.addElements(newElement);

					scope.updateAddBtnState();
				});

				scope.modal.hide();

				$this.addClass(isDisabled);

				// Clear out selection
				scope.selector.deselectAll();
				scope.selector.removeAllItems();
			}
		});
	}
});

})(jQuery);