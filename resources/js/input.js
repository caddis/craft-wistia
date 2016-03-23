(function($) {
	Wistia = {};

	Wistia.VideoSelectInput = Craft.BaseElementSelectInput.extend({
		init: function() {
			this.settings = arguments;

			this.base.apply(this, this.settings);
		},

		removeElements: function($elements)
		{
			if (this.settings.selectable) {
				this.elementSelect.removeItems($elements);
			}

			if (this.modal) {
				var $item = this.modal.$body
					.find('.js-element-tbody')
					.children('[data-id="' + $elements.data('id') + '"]:first');

				$item.enable();
				this.modal.elementSelector.addItems($item);
			}

			// Disable the hidden input in case the form is submitted before
			// this element gets removed from the DOM
			$elements.children('input')
				.prop('disabled', true);

			this.$elements = this.$elements.not($elements);
			this.updateAddElementsBtn();

			this.onRemoveElements();
		},

		createModal: function() {
			return new Wistia.VideoSelectorModal(this, this.settings);
		}
	});

	Wistia.VideoSelectorModal = Craft.BaseElementSelectorModal.extend({
		init: function(elementSelectInput, settings) {
			this.elementSelectInput = elementSelectInput;
			this.settings = settings;

			this.base(null, this.settings);
		},

		onFadeIn: function() {
			if (! this.videoDataLoaded) {
				this._createElementIndex();
			}
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
			if (this.elementSelector.getTotalSelected()) {
				this.elementSelector.getSelectedItems().each($.proxy(function(e, el) {
					var $el = $(el),
						newElement = $el.find('.element')
							.clone()
							.addClass('removable')
							.prepend('<input name="' + this.settings.name + '[]" type="hidden" value="' + $el.data('id') + '">' +
								'<a class="delete icon" title="'+Craft.t('Remove')+'"></a>');

					// Disable items from being selected twice
					$el.disable();
					this.elementSelector.removeItems($el);

					// Add the new element to selected list
					this.elementSelectInput.appendElement(newElement);
					this.elementSelectInput.animateElementIntoPlace($el, newElement);
					this.elementSelectInput.addElements(newElement);
				}, this));

				this.hide();

				// Clear out selection
				this.elementSelector.deselectAll();
			}
		},

		_createElementIndex: function() {
			var $url = Craft.getActionUrl('wistia/video/getModal', {projectIds: this.settings.projectIds});

			Craft.postActionRequest($url, $.proxy(function(response, textStatus) {
				if (textStatus === 'success') {
					this.$body.html(response);

					this._createElementSelector();
					this._createElementSearch();

					this.videoDataLoaded = true;
				}
			}, this));
		},

		_createElementSelector: function() {
			if (! this.elementSelectorCreated) {
				var $container = this.$body.find('.js-element-tbody'),
					disabledIds = this.elementSelectInput.getDisabledElementIds();

				this.$elementRow = this.$body.find('.js-element-tr');

				var sel = $.map(disabledIds, function(key) {
					return '[data-id=' + key + ']';
				});

				this.$elementRow.filter(sel.join(',')).disable();

				this.elementSelector = new Garnish.Select($container,
					this.$elementRow.filter(':not(.disabled)'), {
						filter: ':not(.disabled)',
						onSelectionChange: $.proxy(this, 'updateSelectBtnState')
					}
				);
			}
		},

		_createElementSearch: function() {
			var scope = this,
				$input = scope.$body.find('.search input'),
				isHidden = 'hidden';

			$input.on('keyup', function() {
				var $this = $(this),
					filter = $this.val(),
					$clear = $this.siblings();

				if (filter.length) {
					$clear.removeClass(isHidden);
				} else {
					$clear.addClass(isHidden);
				}

				if (scope.filterSearchTimer) {
					clearTimeout(scope.filterSearchTimer);
				}

				scope.filterSearchTimer = setTimeout(function() {
					scope.$elementRow.each(function(e, el) {
						var $row = $(el);

						// If the row does not contain the text phrase hide it
						if ($row.data('title').search(new RegExp(filter, 'i')) < 0) {
							$row.hide();
						} else {
							// Show the row if the phrase matches
							$row.show();
						}
					});
				}, 300);
			});

			// Clear out text in search input
			$input.siblings('.clear').on('click', function() {
				$(this).addClass(isHidden)
					.siblings('input')
					.val('');

				scope.$elementRow.show();
			});
		}
	});
})(jQuery);