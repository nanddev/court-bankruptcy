/*!
 * jQuery FormShowHide 1.0
 *
 * https://github.com/dkeeghan/jQuery-FormShowHide
 *
 * Copyright 2014 Damian Keeghan
 * Released under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 */

(function($){
	$.formShowHide = function(el, options){
		var base = this;

		base.$el = $(el);
		base.el = el;

		//if the simpleSelect code hasn't already run
		if(typeof(base.$el.data('formShowHide')) === 'undefined'){
			// extend options
			base.options = $.extend({},$.formShowHide.defaultOptions, options);

			//setup base values
			base.$el.data('formShowHide', base);
			base.nodeType = base.el.nodeName.toUpperCase();
			base.inputType = (base.nodeType === 'INPUT') ? base.$el.attr('type').toUpperCase() : '';
			base.showType = '';
			base.showParams = '';

			//initialisation function
			base.init = function(){

				var events = 'change.formShowHide keyup.formShowHide check.formShowHide initCheck.formShowHide';

				if(base.nodeType === 'SELECT'){
					base.$el.off('.formShowHide').on(events, base.onChange);
				} else if(base.nodeType === 'OPTION'){
					base.$el.closest('select').off('.formShowHide').on(events, base.onChange);
				} else if(base.nodeType === 'INPUT'){
					if(base.inputType === 'RADIO'){
						var name = base.$el.attr('name');
						$('input[name="'+name+'"]').off('.formShowHide').on(events, base.onChange);
					} else if(base.inputType === 'CHECKBOX'){
						base.$el.off('.formShowHide').on(events, base.onChange);
					} else if(base.inputType === 'TEXT'){
						base.showType = base.$el.attr(base.options.showTypeAttr);
						base.showParams = base.$el.attr(base.options.showParamsAttr);

						if(base.showType && base.showParams){
							base.showParams = base.convertStrToArr(base.showParams, true);
						}

						base.$el.off('.formShowHide').on(events, base.onChange);
					}
				}

				//do initial showhide check on page load
				base.$el.trigger('initCheck.formShowHide');
			};

			//convert the id string from the element into a jQuery object
			base.convertStrToArr = function(str, isParam){
				isParam = (typeof(isParam) === 'boolean' && isParam === true);

				str = str.replace(/ +(?= )/g,'');

				if(str === ''){
					return [];
				}

				var arr = [];

				if(str.indexOf(' ') === -1){
					arr.push((isParam) ? str : '#'+str);
				} else {
					var strs = str.split(' ');

					$(strs).each(function(i, el){
						arr.push((isParam) ? el : '#'+el);
					});
				}

				return (isParam) ? arr : $.fn.formShowHide.utils.unique(arr);
			};

			//check if the show/hide attribute is defined
			base.checkShowDefined = function($el){
				var str = $el.attr(base.options.showAttr);
				return (typeof(str) === 'undefined') ? '' : str;
			};

			base.checkHideDefined = function($el){
				var str = $el.attr(base.options.hideAttr);
				return (typeof(str) === 'undefined') ? '' : str;
			};

			//get the IDs to show that are referenced on the element
			base.idsToShow = function(){
				var id = '';

				if(base.nodeType === 'SELECT' || base.nodeType === 'OPTION'){
					var $select = (base.nodeType === 'OPTION') ? base.$el.parent() : base.$el;

					//showAttr should be shown if selected only
					id = base.checkShowDefined($select.find('option:selected'));

					//hideAttr should be shown if NOT selected only
					$select.find('option').each(function(i, el){
						if(!$(el).prop('selected')){
							id += ' ' + base.checkHideDefined($(el));
						}
					});
				} else if(base.nodeType === 'INPUT'){
					if(base.inputType === 'RADIO'){
						var name = base.$el.attr('name');

						$('input[name="'+name+'"]').each(function(i, el){
							if($(el).prop('checked')){
								id += base.checkShowDefined($(el));
							} else {
								id += ' ' + base.checkHideDefined($(el));
							}
						});
					} else if(base.inputType === 'CHECKBOX'){
						if(base.$el.prop('checked')){
							id += ' ' + base.checkShowDefined(base.$el);
						} else {
							id += ' ' + base.checkHideDefined($(el));
						}
					} else if(base.inputType === 'TEXT'){
						if(base.showType && base.showParams){
							if(typeof($.formShowHide.types[base.showType]) === 'function' && base.showParams.length > 0){
								if($.formShowHide.types[base.showType](base.el, base.showParams)){
									id = base.checkShowDefined(base.$el);
								}
							}
						}
					}
				}

				if(typeof(id) === 'undefined'){
					//if there aren't any options provided; ignore;
					return [];
				}

				return base.convertStrToArr($.fn.formShowHide.utils.trim(id));
			};

			//get the IDs that are related to the element, but should not be shown
			base.idsToHide = function(idsToShow){
				var id = '';

				if(base.nodeType === 'SELECT' || base.nodeType === 'OPTION'){
					var $select = (base.nodeType === 'OPTION') ? base.$el.parent() : base.$el;

					//hideAttr should be hidden if selected only
					id = base.checkHideDefined($select.find('option:selected'));

					//showAttr should be hidden if NOT selected only
					$select.find('option').each(function(i, el){
						if(!$(el).prop('selected')){
							id += ' ' + base.checkShowDefined($(el));
						}
					});
				} else if(base.nodeType === 'INPUT'){
					if(base.inputType === 'RADIO'){
						var name = base.$el.attr('name');

						$('input[name="'+name+'"]').each(function(i, el){
							if(!$(el).prop('checked')){
								id += ' ' + base.checkShowDefined($(el));
							} else {
								id += ' ' + base.checkHideDefined($(el));
							}
						});
					} else if(base.inputType === 'CHECKBOX'){
						if(!base.$el.prop('checked')){
							id += ' ' + base.checkShowDefined($(el));
						} else {
							id += ' ' + base.checkHideDefined($(el));
						}
					} else if(base.inputType === 'TEXT'){
						if(base.showType && base.showParams){
							if(typeof($.formShowHide.types[base.showType]) === 'function' && base.showParams.length > 0){
								if(!$.formShowHide.types[base.showType](base.el, base.showParams)){
									id = base.checkShowDefined(base.$el);
								}
							}
						}
					}
				}

				var idsToHide = base.convertStrToArr($.fn.formShowHide.utils.trim(id));

				if(idsToShow.length === idsToHide.length && idsToShow.length === 1){
					return idsToHide;
				}

				$(idsToShow).each(function(i, el){
					for(var j in idsToHide){
						if(idsToHide[j] === el){
							idsToHide.splice(j,1);
							return;
						}
					}
				});

				return idsToHide;
			};

			//on change of select element
			base.onChange = function(event){
				var toShow = base.idsToShow(),
					toHide = base.idsToHide(toShow);

				if(toShow.length === toHide.length && $.fn.formShowHide.utils.compare(toShow, toHide)){
					//don't show anything
				} else {
					$(toShow).each(function(i, el){
						if(!$(el).is(':visible')){
							if(event.type === 'initCheck'){
								base.options.initShow(i, el);
							} else {
								base.options.show(i, el);
							}
						} else {
							base.options.showAlreadyVisible(i, el);
						}
					});

				}

				$(toHide).each(function(i, el){
					var afterHide = function(){
						if($(el).hasClass(base.options.resetClass)){
							//trigger keyup for validation, trigger check.formShowHide to trigger internal showhide functionality
							$(el).find('input:text').attr('value', '').trigger('keyup');

							//reset selectboxes
							$(el).find('option:selected').prop('selected', false).end().find('option:first-child').prop('selected', true).trigger('check.formShowHide').trigger('keyup');

							//reset radio/checkboxes
							$(el).find('input:radio, input:checkbox').prop('checked', false).trigger('check.formShowHide').trigger('keyup');
						}
					};

					if(event.type === 'initCheck'){
						base.options.initHide(i, el, afterHide);
					} else {
						base.options.hide(i, el, afterHide);
					}
				});
			};

			//initialise
			base.init();

		}
	};

	//showHide types
	$.formShowHide.types = {
		lessThan: function(elem, params){
			var param = (typeof(parseInt(params[0], 10)) === 'number') ? parseInt(params[0], 10) : 0,
				val = ($(elem).val() === '') ? 0 : parseInt($(elem).val(), 10);

			return (val < param);
		}
	};

	//default options
	$.formShowHide.defaultOptions = {
		show: function(i, el, callback){
			$(el).show();

			if(typeof(callback) === 'function'){
				callback();
			}
		},
		hide: function(i, el, callback){
			$(el).hide();

			if(typeof(callback) === 'function'){
				callback();
			}
		},
		initShow: function(i, el, callback){
			$(el).show();

			if(typeof(callback) === 'function'){
				callback();
			}
		},
		initHide: function(i, el, callback){
			$(el).hide();

			if(typeof(callback) === 'function'){
				callback();
			}
		},
		showAlreadyVisible: function(i, el){
			$(el).show();
		},
		showAttr: 'data-show-id',
		showTypeAttr: 'data-show-type',
		showParamsAttr: 'data-show-params',
		hideAttr: 'data-hide-id',
		resetClass: 'formShowHide_reset'
	};

	$.fn.formShowHide = function(options){
		return this.each(function(){
			(new $.formShowHide(this, options));
		});
	};

	$.fn.formShowHide.utils = {
		unique: function(array){
			var i = 0;

			while (i < array.length){
				for (var k = array.length; k > i; k-=1){
					if (array[k] === array[i]){
						array.splice(k,1);
					}
				}

				i+=1;
			}

			return array;
		},
		trim: function(string){
			return string.replace(/^\s+/, '').replace(/\s+$/, '');
		},
		compare: function(arr1, arr2) {
			if (arr1.length !== arr2.length) { return false; }
			var a = arr1.sort(),
				b = arr2.sort();
			for (var i = 0; arr2[i]; i+=1) {
				if (a[i] !== b[i]) {
					return false;
				}
			}
			return true;
		}
	};

})(jQuery);
