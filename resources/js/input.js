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
		scope.isDisabled = 'disabled';

		scope.elements = new Craft.BaseElementSelectInput({
			id: scope.settings.id,
			limit: scope.settings.max,
			onRemoveElements: function() {
				scope.updateAddBtn();
			}
		});

		scope.updateAddBtn();

		if (scope.$addElementBtn && scope.settings.max === 1) {
			scope.$addElementBtn
				.css('position', 'absolute')
				.css('top', 0)
				.css(Craft.left, 0);
		}

		scope.toggleModal();
	},

	/**
	 * Update add video button
	 */
	updateAddBtn: function() {
		if (this.elements.canAddMoreElements()) {
			this.$addElementBtn.removeClass(this.isDisabled);

			if (this.settings.max === 1) {
				if (this.elements._initialized) {
					this.$addElementBtn.velocity('fadeIn', Craft.BaseElementSelectInput.REMOVE_FX_DURATION);
				} else {
					this.$addElementBtn.show();
				}
			}
		} else {
			this.$addElementBtn.addClass(this.isDisabled);

			if (this.settings.max === 1) {
				if (this.elements._initialized) {
					this.$addElementBtn.velocity('fadeOut', Craft.BaseElementSelectInput.ADD_FX_DURATION);
				} else {
					this.$addElementBtn.hide();
				}
			}
		}
	},

	/**
	 * Open and close modal
	 */
	toggleModal: function() {
		var scope = this;

		scope.$addElementBtn.on('click', function() {
			if (! $(this).hasClass(scope.isDisabled)) {
				if (! scope.modal) {
					$.get(Craft.getActionUrl('wistia/videos/getModal', {projectIds: scope.settings.projectIds}), function(data) {
						scope.modal = new Garnish.Modal($(data)[0]);

						scope.$modalCancel = scope.modal.$container.find('.btn:not(.submit)');
						scope.$modalSubmit = scope.modal.$container.find('.btn.submit');
						scope.$elementRowContainer = scope.modal.$container.find('.data tbody');
						scope.$elementRow = scope.$elementRowContainer.children();

						scope.modal.addListener(scope.$modalCancel, 'click', 'hide');

						scope.updateSelectableElements();

						scope.selector = new Garnish.Select(scope.$elementRowContainer,
							scope.$elementRow.filter(':not(.disabled)'), {
								onSelectionChange: function() {
									scope.updateSelectBtn();
								}
							});

						scope.updateSelectBtn();

						scope.searchElements();

						scope.submitSelections();
					});
				} else {
					scope.updateSelectableElements();

					scope.selector.addItems(scope.$elementRow.filter(':not(.disabled)'));

					scope.modal.show();
				}
			}
		});
	},

	/**
	 * Update which modal elements are selectable
	 */
	updateSelectableElements: function() {
		var scope = this;

		scope.$elementRow.each(function(e, el) {
			var $el = $(el);

			$el.removeClass(scope.isDisabled);

			$(scope.elements.getSelectedElementIds()).each(function(i, val) {
				if ($el.data('id') === val) {
					$el.addClass(scope.isDisabled);
				}
			});
		});
	},

	/**
	 * Update select button based on which modal elements are selected
	 */
	updateSelectBtn: function() {
		if (this.selector.getTotalSelected()) {
			this.$modalSubmit.removeClass(this.isDisabled);
		} else {
			this.$modalSubmit.addClass(this.isDisabled);
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
		var scope = this;

		scope.$modalSubmit.on('click', function() {
			var $this = $(this);

			if (! $this.hasClass(scope.isDisabled)) {
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

					scope.updateAddBtn();
				});

				scope.modal.hide();

				$this.addClass(scope.isDisabled);

				// Clear out selection
				scope.selector.deselectAll();
				scope.selector.removeAllItems();
			}
		});
	}
});

})(jQuery);