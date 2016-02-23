(function($){
	Wistia = {
		/**
		 * Init element selector
		 */
		init: function(settings) {
			this.settings = settings;
			this.elementSelectContainer = $('#' + this.settings.id);
			this.openModalBtn = this.elementSelectContainer.find('.add');
			this.isDisabled = 'disabled';

			this.elements = new Craft.BaseElementSelectInput({
				id: this.settings.id,
				limit: this.settings.max,
				onRemoveElements: function() {
					updateAddBtn();
				}
			});

			this.toggleModal();

			console.log(this);
		},

		/**
		 * Open and close modal
		 */
		toggleModal: function() {
			var scope = this;

			scope.openModalBtn.filter(':not(.' + scope.isDisabled + ')').on('click', function() {
				if (! scope.modal) {
					$.get(scope.getActionUrl('wistia/videos/getModal', {projectIds: scope.settings.projectIds}), function(data) {
						scope.modal = new Garnish.Modal($(data)[0]);

						scope.$modalCancel = scope.modal.$container.find('.btn:not(.submit)');
						scope.$modalSubmit = scope.modal.$container.find('.btn.submit');
						scope.$elementRowContainer = scope.modal.$container.find('.data tbody');
						scope.$elementRow = scope.$elementRowContainer.find('tr');

						scope.modal.addListener(scope.$modalCancel, 'click', 'hide');

						scope.selector = new Garnish.Select(scope.elementRowContainer,
							scope.$elementRow.filter(':not(.' + scope.isDisabled + ')'), {
								onSelectionChange: function() {
									scope.updateSelectBtn();
								}
							});

						scope.updateSelectableElements();

						// TODO: Still working to get these setup
						// searchElements();

						// updateSelectBtn();

						// submitSelections();
					});
				} else {
					scope.modal.show();

					scope.updateSelectableElements();
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
			if (this.selector.getTotalSelected() > 0) {
				this.$modalSubmit.removeClass(this.isDisabled);
			} else {
				this.$modalSubmit.addClass(this.isDisabled);
			}
		}
	};

	/**
	 * Update add video button
	 */
	function updateAddBtn() {
		var $add = $('.js-add');

		// Hide add more elements button if element max is met
		if ($(scope.elements.getSelectedElementIds()).length >= $('.js-element-select').data('max')) {
			$add.addClass(scope.isDisabled);
		} else {
			$add.removeClass(scope.isDisabled);
		}
	}

	/**
	 * Add selected modal elements to final list
	 */
	function submitSelections() {
		$('.js-submit').on('click', function() {
			var $this = $(this),
				$selector = scope.selector,
				$items = $selector.getSelectedItems();

			if (! $this.hasClass(scope.isDisabled)) {
				$items.each(function(e, el) {
					var $el = $(el),
						newElement = $el.find($('.element'))
							.clone()
							.addClass('removable')
							.prepend('<input name="fields[' + $('.js-element-select').data('name') + '][]" type="hidden" value="' + $el.data('id') + '">' +
								'<a class="delete icon" title="'+Craft.t('Remove')+'"></a>');

					// Add new elements to selection list
					scope.elements.appendElement(newElement);

					// Update elements object
					scope.elements.addElements(newElement);

					updateAddBtn();
				});

				// Hide the modal
				scope.modal.hide();

				// Add disabled class to select button
				$this.addClass(scope.isDisabled);

				// Remove selected class from all items
				$selector.deselectAll($items);
			}
		});
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
})(jQuery);